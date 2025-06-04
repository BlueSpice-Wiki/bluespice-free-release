<?php

namespace BlueSpice\SaferEdit;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\LoadBalancer;

class EditWarningBuilder {

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var array
	 */
	protected $intermediateEditUsernames = [];

	/**
	 *
	 * @var string
	 */
	protected $message = '';

	/** @var MediaWikiServices */
	protected $services;

	/**
	 * @param LoadBalancer $loadBalancer
	 * @param Config $config
	 * @param User $user
	 * @param Title $title
	 */
	public function __construct( $loadBalancer, $config, $user, $title ) {
		$this->loadBalancer = $loadBalancer;
		$this->config = $config;
		$this->user = $user;
		$this->title = $title;
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		$this->loadFromDB();
		$this->findIntermediateEdit();
		$this->message = $this->makeMessage();

		$this->services->getHookContainer()->run(
			'BSSaferEditMessage',
			[ $this->title, &$this->message ]
		);

		return $this->message;
	}

	/**
	 * @return string
	 */
	protected function makeMessage(): string {
		if ( empty( $this->intermediateEditUsernames ) ) {
			return '';
		}

		$showName = $this->config->get( 'SaferEditShowNameOfEditingUser' );
		if ( !$showName ) {
			return wfMessage( 'bs-saferedit-someone-editing' )->text();
		}

		$message = wfMessage( 'bs-saferedit-user-editing' )
			->params(
				Message::listParam( $this->intermediateEditUsernames, 'text' ),
				count( $this->intermediateEditUsernames )
			)
			->parse();

		return $message;
	}

	/** @var array */
	protected $intermediateEdits = [];

	protected function loadFromDB() {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'bs_saferedit',
			'*',
			[
				"se_page_title" => $this->title->getDBkey(),
				"se_page_namespace" => $this->title->getNamespace(),
			],
			__METHOD__,
			[ "ORDER BY" => "se_id DESC" ]
		);

		foreach ( $res as $row ) {
			$this->intermediateEdits[] = $row;
		}
	}

	protected function findIntermediateEdit() {
		$interval = $this->getInterval();
		$thresholdTS = wfTimestamp( TS_MW, time() - $interval );
		$currentUserName = $this->user->getName();
		$userFactory = $this->services->getUserFactory();

		foreach ( $this->intermediateEdits as $row ) {
			if ( $row->se_user_name === $currentUserName ) {
				continue;
			}

			if ( $row->se_timestamp < $thresholdTS ) {
				continue;
			}

			$userName = $row->se_user_name;
			$user = $userFactory->newFromName( $userName );
			if ( $user && $user->getRealName() ) {
				$userName = $user->getRealName();
			}
			$this->intermediateEditUsernames[] = $userName;
		}
	}

	/**
	 *
	 * @return int
	 */
	protected function getInterval() {
		$saferEditInterval = $this->config->get( 'SaferEditInterval' );
		$pingInterval = $this->config->get( 'PingInterval' );

		// HINT PW from the ancient times: +1 secound response time is enough
		return $saferEditInterval + $pingInterval + 1;
	}

}
