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
	 * @return array
	 */
	public function getData() {
		$this->loadFromDB();
		$this->findIntermediateEdit();

		$explicitMessage = '';
		$this->services->getHookContainer()->run(
			'BSSaferEditMessage',
			[ $this->title, &$explicitMessage ]
		);
		if ( $explicitMessage ) {
			return [ 'type' => 'explicit', 'message' => $explicitMessage ];
		}

		$data = [];
		if ( !empty( $this->intermediateEditUsernames ) && !$this->config->get( 'SaferEditShowNameOfEditingUser' ) ) {
			$data = [ 'message' => 'bs-saferedit-someone-editing' ];
		} elseif ( !empty( $this->intermediateEditUsernames ) ) {
			$data = [
				'type' => 'with-users',
				'message' => 'bs-saferedit-user-editing',
				'userDisplayNames' => $this->intermediateEditUsernames,
				'users' => array_keys( $this->intermediateEditUsernames ),
			];
		}
		$this->services->getHookContainer()->run(
			'BSSaferEditMessageData',
			[ $this->title, &$data ]
		);
		if ( $data['hideForCurrentUser'] ?? false ) {
			$data['excludeFor'] = $this->user->getName();
			unset( $data['hideForCurrentUser'] );
		}

		return $data;
	}

	/**
	 * Called on page load, not over the wire
	 * @return string
	 */
	public function getMessage(): string {
		$data = $this->getData();
		if ( !$data ) {
			return '';
		}
		$type = $data['type'] ?? '';
		if ( $type === 'explicit' ) {
			return $data['message'];
		}
		if ( $type === 'with-users' ) {
			$users = $data['users'];
			// Filter out $this->user
			$users = array_filter( $users, function ( $user ) {
				return $user !== $this->user->getName();
			} );
			$displayNames = array_map(
				function ( $user ) {
					return $this->intermediateEditUsernames[$user] ?? $user;
				},
				$users
			);
			if ( empty( $displayNames ) ) {
				return '';
			}
			$params = [ Message::listParam( $displayNames, 'text' ), count( $displayNames ) ];
			return Message::newFromKey( $data['message'], ...$params )->text();
		}
		if ( isset( $data['message'] ) ) {
			$params = $data['params'] ?? [];
			return Message::newFromKey( $data['message'], ...$params )->text();
		}
		return '';
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
		$userFactory = $this->services->getUserFactory();
		$hasAnon = false;
		foreach ( $this->intermediateEdits as $row ) {
			$displayName = $row->se_user_name;
			$user = $userFactory->newFromName( $displayName );
			if ( !$user ) {
				if ( $hasAnon ) {
					// No sense in showing multiple anonymous users
					continue;
				}
				$hasAnon = true;
				$user = $userFactory->newAnonymous();
				$displayName = Message::newFromKey( 'bs-saferedit-anonymous-editor' )->text();
			}
			if ( $user->isRegistered() && $user->getRealName() ) {
				$displayName = $user->getRealName();
			}
			$this->intermediateEditUsernames[$user->getName()] = $displayName;
		}
	}

}
