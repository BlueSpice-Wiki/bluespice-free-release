<?php

namespace MediaWiki\Extension\NotifyMe\Channel\Email;

use Exception;
use MediaWiki\Config\Config;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Language\Language;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars;
use Wikimedia\Rdbms\ILoadBalancer;

class MailContentProvider {
	/**
	 * @var ILoadBalancer
	 */
	private $lb;
	/**
	 * @var TitleFactory
	 */
	private $titleFactory;
	/**
	 * @var RevisionLookup
	 */
	private $revisionLookup;
	/**
	 * @var ParserFactory
	 */
	private $parserFactory;

	/** @var Config */
	private $config;

	/** @var UserOptionsLookup */
	private $userOptionsLookup;

	/** @var Language */
	private $language;

	/**
	 * @param ILoadBalancer $lb
	 * @param TitleFactory $titleFactory
	 * @param Config $config
	 * @param RevisionLookup $revisionLookup
	 * @param ParserFactory $parserFactory
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param Language $language
	 */
	public function __construct(
		ILoadBalancer $lb, TitleFactory $titleFactory, Config $config, RevisionLookup $revisionLookup,
		ParserFactory $parserFactory, UserOptionsLookup $userOptionsLookup, Language $language
	) {
		$this->lb = $lb;
		$this->titleFactory = $titleFactory;
		$this->config = $config;
		$this->revisionLookup = $revisionLookup;
		$this->parserFactory = $parserFactory;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->language = $language;
	}

	/**
	 * Get the HTML of the email to be sent
	 *
	 * @param string $type
	 * @param array $serialized
	 * @param User $user
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getFinalEmailHtml( string $type, array $serialized, User $user ): string {
		$content = $this->getContentForType( $type );
		if ( !$content ) {
			throw new Exception( 'No content found for notification type: ' . $type );
		}
		$content = $this->getHtmlFromData( $content, $user, $serialized );
		return $this->wrap( $content, $user );
	}

	/**
	 * @param RevisionRecord $revision
	 *
	 * @return array|null
	 */
	public function getContentForRevision( RevisionRecord $revision ): ?array {
		if ( !$revision->hasSlot( 'mail_template_meta' ) ) {
			return null;
		}

		$meta = $revision->getContent( 'mail_template_meta' );
		$meta = json_decode( $meta->getText(), true );
		return [
			'revision' => $revision,
			'content' => $revision->getContent( 'main' )->getText(),
			'meta' => $meta,
		];
	}

	/**
	 * @param array $data
	 * @param User $user
	 * @param array|null $params
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getHtmlFromData( array $data, User $user, ?array $params = [] ): string {
		$content = $data['content'];
		$this->addLogoToParams( $params );
		$content = $this->maskContent( $content, $params );
		[ $dir, $file ] = $this->writeToTempFile( $content );
		$parser = new TemplateParser( $dir );
		$templateName = substr( $file, 0, -9 );
		$html = $parser->processTemplate( $templateName, $params );
		$html = $this->unmaskHtml( $html );
		$this->processWikitext( $html, $user, $data['revision'] );
		$this->replaceLessVars( $html );
		$this->removeTempFile( $dir, $file );

		return $html;
	}

	/**
	 * @param string $contentHtml
	 * @param User $user
	 *
	 * @return string
	 * @throws Exception
	 */
	public function wrap( string $contentHtml, User $user ): string {
		$content = $this->getContentForType( 'wrapper' );
		if ( !$content ) {
			throw new Exception( 'Mail wrapper not found' );
		}
		return $this->getHtmlFromData( $content, $user, [ 'content' => $contentHtml ] );
	}

	/**
	 * Get a list of all mail content pages (not wrappers)
	 *
	 * @return Title[]
	 */
	public function getEmailContentPages(): array {
		$pages = [];
		$this->forEachMailPage( static function ( Title $title, ?array $content ) use ( &$pages ) {
			if ( $content && isset( $content['meta']['is_content'] ) && $content['meta']['is_content'] ) {
				$pages[] = $title;
			}
		} );
		return $pages;
	}

	/**
	 * @param string $type
	 *
	 * @return array|null
	 */
	private function getContentForType( string $type ): ?array {
		$result = null;
		$this->forEachMailPage( static function ( Title $title, ?array $content ) use ( $type, &$result ) {
			if ( $content && $content['meta']['type'] === $type ) {
				$result = $content;
			}
		} );

		return $result;
	}

	/**
	 * Iterate over each page with `mail_template` content model
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	private function forEachMailPage( $callback ) {
		$db = $this->lb->getConnection( DB_REPLICA );
		$res = $db->select(
			'page',
			[ 'page_namespace', 'page_title' ],
			[
				'page_namespace' => NS_MEDIAWIKI,
				'page_content_model' => 'mail_template',
			],
			__METHOD__
		);

		foreach ( $res as $row ) {
			$title = $this->titleFactory->newFromRow( $row );
			$revision = $this->revisionLookup->getRevisionByTitle( $title );
			$revisionContent = $this->getContentForRevision( $revision );
			call_user_func_array( $callback, [ $title, $revisionContent ] );
		}
	}

	/**
	 * @param string $raw
	 *
	 * @return array
	 * @throws Exception
	 */
	private function writeToTempFile( string $raw ) {
		$dir = wfTempDir();
		$file = md5( $raw ) . '.mustache';
		if ( !file_put_contents( "$dir/$file", $raw ) ) {
			throw new Exception( 'Failed to write to temp file' );
		}
		return [ $dir, $file ];
	}

	/**
	 * @param string $dir
	 * @param string $file
	 *
	 * @return void
	 */
	private function removeTempFile( string $dir, string $file ) {
		unlink( "$dir/$file" );
	}

	/**
	 * @param string &$html
	 * @param User $user
	 * @param RevisionRecord $revision
	 *
	 * @return void
	 */
	private function processWikitext( string &$html, User $user, RevisionRecord $revision ) {
		$parser = $this->parserFactory->create();
		$pageRef = $parser->getPage();
		$parser->setPage( $pageRef );
		$parser->setUser( $user );
		$options = ParserOptions::newFromUser( $user );

		$userLanguage = $this->userOptionsLookup->getOption( $user, 'language' );
		if ( !$userLanguage ) {
			$userLanguage = $this->language;
		}
		$options->setUserLang( $userLanguage );

		$parser->setOptions( $options );
		$html = $parser->preprocess( $html, $pageRef, $options, $revision->getId() );
	}

	/**
	 * @param array &$params
	 *
	 * @return void
	 * @throws Exception
	 */
	private function addLogoToParams( array &$params ) {
		$logo = $this->config->get( 'Logo' );
		if ( $logo ) {
			$urlUtils = MediaWikiServices::getInstance()->getUrlUtils();
			$params['logo'] = $urlUtils->expand( $logo );
		}
	}

	/**
	 * Replace less vars with actual values
	 * to be able to render the email on the other end
	 * @param string &$html
	 *
	 * @return void
	 */
	private function replaceLessVars( string &$html ) {
		$lessVars = LessVars::getInstance();
		$this->setLessVars( $lessVars );

		// Regex all Less vars and replace with value if exists
		$html = preg_replace_callback(
			'/@([a-zA-Z0-9_-]+)/',
			static function ( $matches ) use ( $lessVars ) {
				$var = $matches[1];
				$value = $lessVars->getVar( $var );
				if ( $value ) {
					return $value;
				}
				return $matches[0];
			},
			$html
		);
	}

	/**
	 * @param string $content
	 * @param array $params
	 *
	 * @return string
	 */
	private function maskContent( string $content, array $params ): string {
		// Replace all `{{int:...}}` with `>>>int:...<<<` to avoid parsing by the parser
		return preg_replace( '/{{int:(.*?)}}/', '>>>int:$1<<<', $content );
	}

	/**
	 * @param string $html
	 *
	 * @return string
	 */
	private function unmaskHtml( string $html ): string {
		// Replace all `>>>int:...<<<` with `{{int:...}}`
		$html = preg_replace( '/>>>int:(.*?)<<</', '{{int:$1}}', $html );
		return preg_replace( '/>>>(.*?)<<</', '{{$1}}', $html );
	}

	/**
	 * @param LessVars $lessVars
	 * @return void
	 */
	public function setLessVars( LessVars $lessVars ) {
		$this->setLessVarFromParent( $lessVars, 'notifications-mail-color-primary-bg', 'content-bg', 'white' );
		$this->setLessVarFromParent( $lessVars, 'notifications-mail-color-header-bg', 'navbar-bg', '#c2c2c2' );
		$this->setLessVarFromParent( $lessVars, 'notifications-mail-color-background', 'sidebar-bg', '#c2c2c2' );
		$this->setLessVarFromParent( $lessVars, 'notifications-mail-color-text', 'content-fg', 'black' );
		$this->setLessVarFromParent( $lessVars, 'notifications-mail-color-footer-bg', 'footer-bg', '#c2c2c2' );
		$this->setLessVarFromParent( $lessVars, 'notifications-mail-color-footer-fg', 'footer-fg', 'black' );
	}

	/**
	 * @param LessVars $lessVars
	 * @param string $var
	 * @param string $parentVar
	 * @param string $default
	 *
	 * @return void
	 */
	private function setLessVarFromParent( LessVars $lessVars, string $var, string $parentVar, string $default ) {
		$parent = $lessVars->getVar( $parentVar );
		if ( $parent ) {
			$lessVars->setVar( $var, $parent );
		} else {
			$lessVars->setVar( $var, $default );
		}
	}
}
