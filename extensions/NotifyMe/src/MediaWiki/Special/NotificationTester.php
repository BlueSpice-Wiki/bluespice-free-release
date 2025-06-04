<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Special;

use Exception;
use MediaWiki\Extension\NotifyMe\NotificationTester as Tester;
use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\FormSpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\Events\BotAgent;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;

class NotificationTester extends FormSpecialPage {

	/** @var Tester */
	private $tester;

	/** @var UserFactory */
	private $userFactory;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param Tester $tester
	 * @param UserFactory $userFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( Tester $tester, UserFactory $userFactory, TitleFactory $titleFactory ) {
		parent::__construct( 'NotificationTester', 'wikiadmin', false );
		$this->tester = $tester;
		$this->userFactory = $userFactory;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param string $par
	 * @return void
	 */
	public function execute( $par ) {
		$this->getOutput()->enableOOUI();
		$this->addBanner();
		parent::execute( $par );
		$this->outputEventsTable();
	}

	/**
	 * @return array[]
	 */
	protected function getFormFields() {
		return [
			'event' => [
				'type' => 'user',
				'default' => '',
				'label-message' => 'notifyme-notification-tester-field-event',
				'name' => 'event',
				'required' => true,
			],
			'event-info' => [
				'type' => 'info',
				'label' => $this->getContext()->msg( 'notifyme-notification-tester-event-info' )->text(),
			],
			'bot' => [
				'type' => 'check',
				'default' => 0,
				'label-message' => 'notifyme-notification-tester-field-bot',
				'name' => 'bot',
				'help-message' => 'notifyme-notification-tester-field-bot-help',
				'required' => false,
			],
			'agent' => [
				'type' => 'user',
				'default' => $this->getUser()->getName(),
				'label-message' => 'notifyme-notification-tester-field-agent',
				'name' => 'agent',
				'required' => true,
			],
			'title' => [
				'type' => 'title',
				'default' => Title::newMainPage()->getPrefixedText(),
				'label-message' => 'notifyme-notification-tester-field-title',
				'name' => 'page',
				'required' => false,
			],
			'target-user' => [
				'type' => 'user',
				'default' => 'WikiSysop',
				'label-message' => 'notifyme-notification-tester-field-target-user',
				'name' => 'target-user',
				'required' => false,
			],
		];
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function onSubmit( array $data ) {
		if ( $data['bot'] ) {
			$agent = new BotAgent();
		} else {
			$agent = $this->userFactory->newFromName( $data['agent'] );
		}
		if ( !$agent ) {
			$this->displayError( 'notifyme-notification-tester-invalid-agent' );
			return;
		}
		$page = null;
		$targetUser = null;
		if ( $data['title'] ) {
			$page = $this->titleFactory->newFromText( $data['title'] );
			if ( !$page || !$page->exists() ) {
				$this->displayError( 'notifyme-notification-tester-invalid-page' );
				return;
			}
		}
		if ( $data['target-user'] ) {
			$targetUser = $this->userFactory->newFromName( $data['target-user'] );
			if ( !$targetUser ) {
				$this->displayError( 'notifyme-notification-tester-invalid-target-user' );
				return;
			}
		}

		try {
			$this->tester->triggerForKey( $data['event'], $agent, $page, $targetUser );
		} catch ( Exception $e ) {
			$this->displayError( $e->getMessage() );
			return;
		}
	}

	/**
	 * @return string
	 */
	protected function getDisplayFormat() {
		return 'ooui';
	}

	private function outputEventsTable() {
		$specs = $this->tester->getEventSpecs();

		$html = Html::openElement( 'table', [ 'class' => 'wikitable' ] );
		$html .= Html::openElement( 'tr' );
		$html .= Html::element( 'th', [], $this->msg( 'notifyme-notification-tester-event' ) );
		$html .= Html::element( 'th', [], $this->msg( 'notifyme-notification-tester-buckets' ) );
		$html .= Html::element( 'th', [], $this->msg( 'notifyme-notification-tester-testable' ) );
		$html .= Html::closeElement( 'tr' );
		foreach ( $specs as $eventKey => $spec ) {
			$isTestable = ( !isset( $spec['testable'] ) || $spec['testable'] === true ) && isset( $spec['spec'] );
			$buckets = $spec['buckets'] ?? [];
			$html .= Html::openElement( 'tr' );
			$html .= Html::element( 'td', [], $eventKey );
			$html .= Html::element( 'td', [], implode( ', ', $buckets ) );
			$html .= Html::element( 'td', [], $isTestable ? 'Y' : 'N' );
			$html .= Html::closeElement( 'tr' );
		}
		$html .= Html::closeElement( 'table' );

		$this->getOutput()->addHTML( $html );
	}

	private function addBanner() {
		$this->getOutput()->addHTML(
			new MessageWidget( [
				'type' => 'info',
				'label' => new HtmlSnippet( $this->msg( 'notifyme-notification-tester-banner' )->parse() )
			] )
		);
	}

	/**
	 * @param string $msg
	 * @return void
	 */
	private function displayError( string $msg ) {
		$this->getOutput()->addHTML(
			new MessageWidget( [
				'type' => 'error',
				'label' => $this->msg( $msg )
			] )
		);
	}

}
