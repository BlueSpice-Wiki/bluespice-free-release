<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Content;

use Article;
use Exception;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\NotifyMe\Channel\Email\MailContentProvider;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Title\Title;

class MailTemplate extends TextContent {
	/**
	 * @var MailContentProvider
	 */
	private $mailContentProvider;
	/**
	 * @var RevisionLookup
	 */
	private $revisionLookup;

	/**
	 * @param string $text
	 *
	 * @throws Exception
	 */
	public function __construct( $text ) {
		parent::__construct( $text, 'mail_template' );
		$this->revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$this->mailContentProvider = MediaWikiServices::getInstance()->getService(
			'NotifyMe.MailContentProvider'
		);
	}

	/**
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 *
	 * @return void
	 */
	protected function fillParserOutput(
		Title $title, $revId, ParserOptions $options, $generateHtml, ParserOutput &$output
	) {
		if ( $this->isRedirect() ) {
			$destTitle = $this->getRedirectTarget();
			if ( $destTitle instanceof Title ) {
				$output->addLink( $destTitle );
				if ( $generateHtml ) {
					$output->setText(
						Article::getRedirectHeaderHtml( $title->getPageLanguage(), $destTitle )
					);
					$output->addModuleStyles( [ 'mediawiki.action.view.redirectPage' ] );
				}
			}
			return;
		}

		try {
			$revision = $this->revisionLookup->getRevisionByTitle( $title, $revId );
			if ( !$revision ) {
				throw new Exception( 'No revision found' );
			}
			$content = $this->mailContentProvider->getContentForRevision( $revision );
			if ( !$content ) {
				throw new Exception( 'No content found for single notification' );
			}
			$type = $content['meta']['type'];
			if ( isset( $content['meta']['is_content'] ) && $content['meta']['is_content'] ) {
				$contentHtml = $this->mailContentProvider->getHtmlFromData(
					$content,
					RequestContext::getMain()->getUser(),
					$content['meta']['sampleData'] ?? []
				);
			} else {
				$contentHtml = $content['meta']['sampleData']['content'] ?? '';
			}
			$html = $this->mailContentProvider->wrap(
				$contentHtml, RequestContext::getMain()->getUser()
			);

		} catch ( Exception $e ) {
			$type = 'single';
			$html = $this->getText();
		}

		$text = Html::rawElement( 'p', [
			'style' => 'font-size: 1.2em',
		], Message::newFromKey( 'notifyme-mail-template-output-header-' . $type )->parse() );
		if ( $type === 'wrapper' ) {
			$this->addContentPages( $text );
		}

		$output->setText( $text . Html::rawElement( 'iframe', [
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
		$pages = $this->mailContentProvider->getEmailContentPages();
		$text .= Html::openElement( 'ul' );
		foreach ( $pages as $page ) {
			$text .= Html::rawElement( 'li', [], Html::element( 'a', [
				'href' => $page->getLocalURL(),
			], $page->getText() ) );
		}
		$text .= Html::closeElement( 'ul' );

		return $text;
	}

}
