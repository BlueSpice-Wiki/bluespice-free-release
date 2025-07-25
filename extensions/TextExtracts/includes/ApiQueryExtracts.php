<?php

namespace MediaWiki\Extension\TextExtracts;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryBase;
use MediaWiki\Api\ApiUsageException;
use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Languages\LanguageConverterFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\TitleFormatter;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\ParamValidator\ParamValidator;
use WikiPage;

/**
 * @license GPL-2.0-or-later
 */
class ApiQueryExtracts extends ApiQueryBase {

	/**
	 * Bump when memcache needs clearing
	 */
	private const CACHE_VERSION = 2;

	private const PREFIX = 'ex';

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @var Config
	 */
	private $config;
	/**
	 * @var WANObjectCache
	 */
	private $cache;
	/**
	 * @var LanguageConverterFactory
	 */
	private $langConvFactory;
	/**
	 * @var WikiPageFactory
	 */
	private $wikiPageFactory;
	private TitleFormatter $titleFormatter;

	// TODO: Allow extensions to hook into this to opt-in.
	// This is partly for security reasons; see T107170.
	/**
	 * @var string[]
	 */
	private $supportedContentModels = [ 'wikitext' ];

	/**
	 * @param ApiQuery $query API query module object
	 * @param string $moduleName Name of this query module
	 * @param ConfigFactory $configFactory
	 * @param WANObjectCache $cache
	 * @param LanguageConverterFactory $langConvFactory
	 * @param WikiPageFactory $wikiPageFactory
	 * @param TitleFormatter $titleFormatter
	 */
	public function __construct(
		$query,
		$moduleName,
		ConfigFactory $configFactory,
		WANObjectCache $cache,
		LanguageConverterFactory $langConvFactory,
		WikiPageFactory $wikiPageFactory,
		TitleFormatter $titleFormatter
	) {
		parent::__construct( $query, $moduleName, self::PREFIX );
		$this->config = $configFactory->makeConfig( 'textextracts' );
		$this->cache = $cache;
		$this->langConvFactory = $langConvFactory;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->titleFormatter = $titleFormatter;
	}

	/**
	 * Evaluates the parameters, performs the requested extraction of text,
	 * and sets up the result
	 */
	public function execute() {
		$titles = $this->getPageSet()->getGoodPages();
		if ( $titles === [] ) {
			return;
		}
		$isXml = $this->getMain()->isInternalMode()
			|| $this->getMain()->getPrinter()->getFormat() == 'XML';
		$result = $this->getResult();
		$params = $this->params = $this->extractRequestParams();
		$params['intro'] = $this->params['intro'] = false;
		$this->requireMaxOneParameter( $params, 'chars', 'sentences' );
		$continue = 0;
		$limit = intval( $params['limit'] );
		if ( $limit > 1 && !$params['intro'] && count( $titles ) > 1 ) {
			$limit = 1;
			$this->addWarning( [ 'apiwarn-textextracts-limit', $limit ] );
		}
		if ( isset( $params['continue'] ) ) {
			$continue = intval( $params['continue'] );
			$this->dieContinueUsageIf( $continue < 0 || $continue > count( $titles ) );
			$titles = array_slice( $titles, $continue, null, true );
		}
		$count = 0;
		$titleInFileNamespace = false;
		/** @var PageIdentity $t */
		foreach ( $titles as $id => $t ) {
			if ( ++$count > $limit ) {
				$this->setContinueEnumParameter( 'continue', $continue + $count - 1 );
				break;
			}

			if ( $t->getNamespace() === NS_FILE ) {
				$text = '';
				$titleInFileNamespace = true;
			} else {
				$params = $this->params;
				$text = $this->getExtract( $t );
				$text = $this->truncate( $text );
				if ( $params['plaintext'] ) {
					$text = $this->doSections( $text );
				} else {
					if ( $params['sentences'] ) {
						$this->addWarning( $this->msg( 'apiwarn-textextracts-sentences-and-html', self::PREFIX ) );
					}
					$this->addWarning( 'apiwarn-textextracts-malformed-html' );
				}
			}

			if ( $isXml ) {
				$fit = $result->addValue( [ 'query', 'pages', $id ], 'extract', [ '*' => $text ] );
			} else {
				$fit = $result->addValue( [ 'query', 'pages', $id ], 'extract', $text );
			}
			if ( !$fit ) {
				$this->setContinueEnumParameter( 'continue', $continue + $count - 1 );
				break;
			}
		}
		if ( $titleInFileNamespace ) {
			$this->addWarning( 'apiwarn-textextracts-title-in-file-namespace' );
		}
	}

	/**
	 * @param array $params Ignored parameters
	 * @return string
	 */
	public function getCacheMode( $params ) {
		return 'public';
	}

	/**
	 * Returns a processed, but not trimmed extract
	 * @param PageIdentity $title
	 * @return string
	 */
	private function getExtract( PageIdentity $title ) {
		$page = $this->wikiPageFactory->newFromTitle( $title );

		$contentModel = $page->getContentModel();
		if ( !in_array( $contentModel, $this->supportedContentModels, true ) ) {
			$this->addWarning( [
				'apiwarn-textextracts-unsupportedmodel',
				wfEscapeWikiText( $this->titleFormatter->getPrefixedText( $title ) ),
				$contentModel
			] );
			return '';
		}

		$introOnly = $this->params['intro'];
		$text = $this->getFromCache( $page, $introOnly );
		// if we need just first section, try retrieving full page and getting first section out of it
		if ( $text === false && $introOnly ) {
			$text = $this->getFromCache( $page, false );
			if ( $text !== false ) {
				$text = $this->getFirstSection( $text, $this->params['plaintext'] );
			}
		}
		if ( $text === false ) {
			$text = $this->parse( $page );
			$text = $this->convertText( $text );
			$this->setCache( $page, $text );
		}
		return $text;
	}

	/**
	 * @param WANObjectCache $cache
	 * @param WikiPage $page
	 * @param bool $introOnly
	 * @return string
	 */
	private function cacheKey( WANObjectCache $cache, WikiPage $page, $introOnly ) {
		$langConv = $this->langConvFactory->getLanguageConverter( $page->getTitle()->getPageLanguage() );
		return $cache->makeKey( 'textextracts', self::CACHE_VERSION,
			$page->getId(), $page->getTouched(),
			$langConv->getPreferredVariant(),
			$this->params['plaintext'] ? 'plaintext' : 'html',
			$introOnly ? 'intro' : 'full'
		);
	}

	/**
	 * @param WikiPage $page
	 * @param bool $introOnly
	 * @return string|false
	 */
	private function getFromCache( WikiPage $page, $introOnly ) {
		$cache = $this->cache;
		// @TODO: replace with getWithSetCallback()
		$key = $this->cacheKey( $cache, $page, $introOnly );
		return $cache->get( $key );
	}

	/**
	 * @param WikiPage $page
	 * @param string $text
	 */
	private function setCache( WikiPage $page, $text ) {
		$cache = $this->cache;
		// @TODO: replace with getWithSetCallback()
		$key = $this->cacheKey( $cache, $page, $this->params['intro'] );
		$cache->set( $key, $text, $this->getConfig()->get( 'ParserCacheExpireTime' ) );
	}

	/**
	 * @param string $text
	 * @param bool $plainText
	 * @return string
	 */
	private function getFirstSection( $text, $plainText ) {
		if ( $plainText ) {
			$regexp = '/^.*?(?=' . ExtractFormatter::SECTION_MARKER_START .
				'(?!.' . ExtractFormatter::SECTION_MARKER_END . '<h2 id="mw-toc-heading"))/s';
		} else {
			$regexp = '/^.*?(?=<h[1-6]\b(?! id="mw-toc-heading"))/s';
		}
		if ( preg_match( $regexp, $text, $matches ) ) {
			$text = $matches[0];
		}
		return $text;
	}

	/**
	 * Returns page HTML
	 * @param WikiPage $page
	 * @return string
	 * @throws ApiUsageException
	 */
	private function parse( WikiPage $page ) {
		$parserOutputAccess = MediaWikiServices::getInstance()->getParserOutputAccess();
		$status = $parserOutputAccess->getParserOutput(
			$page->toPageRecord(),
			ParserOptions::newFromAnon()
		);
		if ( $status->isOK() ) {
			$pout = $status->getValue();
			$text = $pout->getText( [ 'unwrap' => true ] );
			if ( $this->params['intro'] ) {
				$text = $this->getFirstSection( $text, false );
			}
			return $text;
		} else {
			LoggerFactory::getInstance( 'textextracts' )->warning(
				'Parse attempt failed while generating text extract', [
					'title' => $page->getTitle()->getFullText(),
					'url' => $this->getRequest()->getFullRequestURL(),
					'reason' => $status->getWikiText( false, false, 'en' )
				] );
			$this->dieStatus( $status );
		}
	}

	/**
	 * Converts page HTML into an extract
	 * @param string $text
	 * @return string
	 */
	private function convertText( $text ) {
		$fmt = new ExtractFormatter( $text, $this->params['plaintext'] );
		$fmt->remove( $this->config->get( 'ExtractsRemoveClasses' ) );
		$text = $fmt->getText();
		return $text;
	}

	/**
	 * Truncate the given text to a certain number of characters or sentences
	 * @param string $text The text to truncate
	 * @return string
	 */
	private function truncate( $text ) {
		$useTidy = !$this->params['plaintext'];
		$truncator = new TextTruncator( $useTidy );

		if ( $this->params['chars'] ) {
			$truncatedText = $truncator->getFirstChars( $text, $this->params['chars'] );
			if ( $truncatedText !== $text ) {
				$text = $truncatedText . $this->msg( 'ellipsis' )->text();
			}
		} elseif ( $this->params['sentences'] ) {
			$text = $truncator->getFirstSentences( $text, $this->params['sentences'] );
		}
		return $text;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function doSections( $text ) {
		$pattern = '/' .
			ExtractFormatter::SECTION_MARKER_START . '(\d)' .
			ExtractFormatter::SECTION_MARKER_END . '(.*)/';

		switch ( $this->params['sectionformat'] ) {
			case 'raw':
				return $text;

			case 'wiki':
				return preg_replace_callback( $pattern, static function ( $matches ) {
					$bars = str_repeat( '=', $matches[1] );
					return "\n$bars " . trim( $matches[2] ) . " $bars";
				}, $text );

			case 'plain':
				return preg_replace_callback( $pattern, static function ( $matches ) {
					return "\n" . trim( $matches[2] );
				}, $text );

			default:
				throw new \LogicException( 'Invalid sectionformat' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams() {
		return [
			'chars' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => 1200,
			],
			'sentences' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => 10,
			],
			'limit' => [
				ParamValidator::PARAM_DEFAULT => 20,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => 20,
				ApiBase::PARAM_MAX2 => 20,
			],
			'intro' => false,
			'plaintext' => false,
			'sectionformat' => [
				ApiBase::PARAM_TYPE => [ 'plain', 'wiki', 'raw' ],
				ParamValidator::PARAM_DEFAULT => 'wiki',
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [],
			],
			'continue' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&prop=extracts&exchars=175&titles=Therion'
				=> 'apihelp-query+extracts-example-1',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:TextExtracts#API';
	}

}
