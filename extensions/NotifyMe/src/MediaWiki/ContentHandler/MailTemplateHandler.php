<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ContentHandler;

use Exception;
use MediaWiki\Content\Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\TextContentHandler;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\NotifyMe\Channel\Email\MailContentProvider;
use MediaWiki\Extension\NotifyMe\MediaWiki\Action\EditMailTemplateAction;
use MediaWiki\Extension\NotifyMe\MediaWiki\Content\MailTemplate;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Revision\RevisionLookup;

class MailTemplateHandler extends TextContentHandler {

	/**
	 * @param string|null $modelId
	 */
	public function __construct(
		string $modelId,
		private readonly RevisionLookup $revisionLookup,
		private readonly MailContentProvider $mailContentProvider,
	) {
		$modelId = $modelId ?: 'mail_template';
		parent::__construct( $modelId, [ CONTENT_FORMAT_HTML ] );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return MailTemplate::class;
	}

	protected function fillParserOutput( Content $content, ContentParseParams $cpoParams, ParserOutput &$output ) {
		$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$mailContentProvider = MediaWikiServices::getInstance()->getService(
			'NotifyMe.MailContentProvider'
		);
		try {
			$page = MediaWikiServices::getInstance()->getTitleFactory()->castFromPageReference(
				$cpoParams->getPage()
			);
			$revision = $revisionLookup->getRevisionByTitle( $page, $cpoParams->getRevId() );
			if ( !$revision ) {
				throw new Exception( 'No revision found' );
			}
			$mailContent = $mailContentProvider->getContentForRevision( $revision );
			if ( !$mailContent ) {
				throw new Exception( 'No content found for single notification' );
			}
			$type = $mailContent['meta']['type'];
			if ( isset( $mailContent['meta']['is_content'] ) && $mailContent['meta']['is_content'] ) {
				$mailContentHtml = $mailContentProvider->getHtmlFromData(
					$mailContent,
					RequestContext::getMain()->getUser(),
					$mailContent['meta']['sampleData'] ?? []
				);
			} else {
				$mailContentHtml = $mailContent['meta']['sampleData']['content'] ?? '';
			}
			$html = $mailContentProvider->wrap(
				$mailContentHtml, RequestContext::getMain()->getUser()
			);
		} catch ( Exception $e ) {
			$type = 'single';
			$html = $content->getText();
		}

		$text = Html::rawElement( 'p', [
			'style' => 'font-size: 1.2em',
		], Message::newFromKey( 'notifyme-mail-template-output-header-' . $type )->parse() );
		if ( $type === 'wrapper' ) {
			$this->addContentPages( $text );
		}

		$output->setRawText( $text . Html::rawElement( 'iframe', [
				'id' => 'mail-template',
				'src' => 'about:blank',
				'width' => '100%',
				'style' => 'min-height: 800px;',
				'data-html' => $html,
			] ) );
		$output->addModules( [ 'ext.notifyme.mailTemplate' ] );
	}

	/**
	 * Add a list of content pages, in case user is on wrapper page
	 *
	 * @param string &$text
	 *
	 * @return string
	 */
	private function addContentPages( string &$text ) {
		$mailContentProvider = MediaWikiServices::getInstance()->getService(
			'NotifyMe.MailContentProvider'
		);
		$pages = $mailContentProvider->getEmailContentPages();
		$text .= Html::openElement( 'ul' );
		foreach ( $pages as $page ) {
			$text .= Html::rawElement( 'li', [], Html::element( 'a', [
				'href' => $page->getLocalURL(),
			], $page->getText() ) );
		}
		$text .= Html::closeElement( 'ul' );

		return $text;
	}

	/**
	 * @return false
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCategories() {
		return true;
	}

	/**
	 * @return false
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 * @return string[]
	 */
	public function getActionOverrides() {
		return [
			'edit' => EditMailTemplateAction::class,
		];
	}
}
