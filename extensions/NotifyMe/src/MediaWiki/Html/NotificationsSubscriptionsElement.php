<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Html;

use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLFormField;
use MWException;
use OOUI\ProgressBarWidget;

class NotificationsSubscriptionsElement extends HTMLFormField {
	/**
	 * @var string
	 */
	private $value;
	/**
	 * @var array
	 */
	private $additionalRLModules;

	/**
	 * @param array $params
	 *
	 * @throws MWException
	 */
	public function __construct( $params ) {
		parent::__construct( $params );
		$this->value = $params['value'];
		$this->additionalRLModules = $params['rl-modules'] ?? [];
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function getInputHTML( $value ) {
		return $this->getInput( false );
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function getInputOOUI( $value ) {
		return $this->getInput();
	}

	/**
	 * @param bool $ooUI
	 *
	 * @return string
	 */
	private function getInput( ?bool $ooUI = true ): string {
		$this->mParent->getOutput()->addModules(
			[ 'ext.notifyme.subscription-preferences' ]
		);
		if ( $this->additionalRLModules ) {
			$this->mParent->getOutput()->addModules( $this->additionalRLModules );
		}
		$content = Html::element( 'h4', [
			'style' => 'margin-top: 0;',
		], $this->msg( 'notifications-subscriptions' )->text() );
		if ( $ooUI ) {
			$content .= new ProgressBarWidget( [
				'progress' => false,
				'classes' => [ 'mw-notifications-subscriptions-progress' ]
			] );
		}
		// Cannot be `hidden`, as it does not maintain `defaultValue`
		// Which is used to recognize changes in the form
		$underlyingField = Html::input(
			'wpext-notification-subscriptions',
			json_encode( $this->value['configuration'] ),
			'text',
			[
				'style' => 'display:none;',
				'class' => 'ext-notifyme-subscriptions-hidden'
			]
		);

		return Html::rawElement( 'div', [
			'class' => 'notifications-subscriptions',
			'data-buckets' => json_encode( $this->value['bucketData'] ),
			'data-events' => json_encode( $this->value['eventData' ] ),
			'data-channel-labels' => json_encode( $this->value['channelLabels'] ),
		], $content . $underlyingField );
	}
}
