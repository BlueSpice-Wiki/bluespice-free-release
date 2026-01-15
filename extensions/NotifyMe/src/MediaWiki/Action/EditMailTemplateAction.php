<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Action;

use EditAction;
use LogicException;
use MediaWiki\EditPage\EditPage;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;
use OOUI\Exception;

class EditMailTemplateAction extends EditAction {

	/**
	 * @return void
	 * @throws Exception
	 */
	public function show() {
		$this->useTransactionalTimeLimit();
		try {
			if ( !$this->getTitle()->exists() ) {
				throw new LogicException();
			}
			$meta = $this->getMeta();
		} catch ( \Exception $e ) {
			$action = 'create';
			if ( $this->getTitle()->exists() ) {
				$action = 'edit';
			}
			$this->getOutput()->showErrorPage(
				'error', 'notifyme-edit-mail-template-error-' . $action
			);
			return;
		}

		$out = $this->getOutput();
		$out->setRobotPolicy( 'noindex,nofollow' );
		$out->disableClientCache();

		$this->addHelp( $meta['type'] );

		$article = $this->getArticle();
		if ( $this->getHookRunner()->onCustomEditor( $article, $this->getUser() ) ) {
			$editor = new EditPage( $article );
			$editor->suppressIntro = true;
			$editor->setContextTitle( $this->getTitle() );
			$editor->edit();
		}

		$this->getOutput()->setPageTitle(
			$this->getContext()->msg( 'notifyme-mail-template-edit-title-' . $meta['type'] )
		);
	}

	/**
	 * @return mixed
	 * @throws LogicException
	 */
	private function getMeta() {
		$rev = $this->getArticle()->getPage()->getRevisionRecord();
		if ( !$rev->hasSlot( 'mail_template_meta' ) ) {
			throw new LogicException( 'Trying to edit an invalid mail template' );
		}
		$meta = $rev->getContent( 'mail_template_meta' );
		return json_decode( $meta->getText(), true );
	}

	/**
	 * @param string $type
	 *
	 * @return void
	 */
	private function addHelp( string $type ) {
		/** @var NotificationSerializer $serializer */
		$serializer = MediaWikiServices::getInstance()->getService( 'NotifyMe.Serializer' );
		$supportedParams = $serializer->getPublicSchemaFor( $type === 'digest' ? 'group' : 'notification' );

		// Colors
		$colors = [];
		$lessVars = LessVars::getInstance();
		// Make sure to set the less vars used in mails from parent values
		MediaWikiServices::getInstance()->getService( 'NotifyMe.MailContentProvider' )->setLessVars( $lessVars );
		foreach ( $lessVars->getAllVars() as $key => $value ) {
			if ( strpos( $key, 'notifications-mail-color-' ) === 0 ) {
				$colors[$key] = $value;
			}
		}

		$this->getOutput()->addJsConfigVars( 'wgNotificationsMailTemplateHelp', [
			'params' => $supportedParams,
			'colors' => $colors,
		] );
		$this->getOutput()->addModules( 'ext.notifyme.mailTemplateHelp' );
	}
}
