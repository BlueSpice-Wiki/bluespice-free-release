<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\CssContent;
use MediaWiki\Content\JsonContent;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\PDFCreator\MediaWiki\Content\PDFCreatorTemplate;
use MediaWiki\Extension\PDFCreator\PDFCreatorUtil;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

class TemplateValueInsertor {

	/** @var string */
	private static $TEMPLATE_DIR;

	/** @var PDFCreatorUtil */
	private $pdfCreatorUtil;

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/** @var bool */
	private $createNewTemplate;

	/** @var Title */
	private $pdfTemplateTitle = null;

	/** @var array */
	private $slotData;

	/** @var array */
	private $errors;

	/**
	 * @param PDFCreatorUtil $pdfCreatorUtil
	 * @param RevisionLookup $revisionLookup
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct( PDFCreatorUtil $pdfCreatorUtil, RevisionLookup $revisionLookup,
		WikiPageFactory $wikiPageFactory ) {
		$this->pdfCreatorUtil = $pdfCreatorUtil;
		$this->revisionLookup = $revisionLookup;
		$this->wikiPageFactory = $wikiPageFactory;

		self::$TEMPLATE_DIR = realpath( __DIR__ . '/../../data/PDFTemplates' );

		$this->createNewTemplate = false;
		$this->slotData = [];
	}

	/**
	 * @param string $templateName
	 * @param array $data
	 * @param UserIdentity $user
	 * @return Status
	 */
	public function saveTemplateValues( $templateName, $data, $user ) {
		$this->pdfTemplateTitle = $this->pdfCreatorUtil->createPDFTemplateTitle( $templateName );
		if ( !$this->pdfTemplateTitle ) {
			return Status::newFatal( 'pdfcreator-edit-template-values-invalid-template' );
		}
		if ( !$this->pdfTemplateTitle->exists() ) {
			$this->createNewTemplate = true;
		}

		$this->slotData = [];
		$this->slotData['options'] = json_encode( $data['general']['options'] );
		$this->slotData['main'] = $data['general']['desc'];

		if ( $data[ 'header' ] ) {
			$headerProcessed = $this->processHeader( $data[ 'header' ] );
			if ( !$headerProcessed ) {
				return Status::newFatal( $this->errors['header'] );
			}
		}
		if ( $data[ 'footer' ] ) {
			$footerProcessed = $this->processFooter( $data[ 'footer' ] );
			if ( !$footerProcessed ) {
				return Status::newFatal( $this->errors['footer'] );
			}
		}
		if ( $data[ 'intro' ] ) {
			$introProcessed = $this->processIntro( $data[ 'intro' ] );
			if ( !$introProcessed ) {
				return Status::newFatal( $this->errors['intro'] );
			}
		}
		if ( $data['outro' ] ) {
			$outroProcessed = $this->processOutro( $data[ 'outro' ] );
			if ( !$outroProcessed ) {
				return Status::newFatal( $this->errors['outro'] );
			}
		}
		$this->processStyles( $data['general']['size'] );

		$saveStatus = $this->save( $user );
		return $saveStatus;
	}

	/**
	 * @param UserIdentity $user
	 * @return Status
	 */
	private function save( $user ) {
		$wikipage = $this->wikiPageFactory->newFromTitle( $this->pdfTemplateTitle );
		$updater = $wikipage->newPageUpdater( $user );

		$this->slotData['body'] = $this->getDefaultContent( 'body' );
		foreach ( $this->pdfCreatorUtil->slots as $slot ) {
			if ( !array_key_exists( $slot, $this->slotData ) ) {
				$this->slotData[ $slot ] = '';
			}
		}

		foreach ( $this->slotData as $slotKey => $content ) {
			if ( $slotKey === 'styles' ) {
				$content = new CssContent( $content );
			} elseif ( $slotKey === 'options' ) {
				$content = new JsonContent( $content );
			} else {
				$content = new PDFCreatorTemplate( $content );
			}
			$slot = $this->pdfCreatorUtil->templatePrefix . $slotKey;
			if ( $slotKey === 'main' ) {
				$slot = 'main';
			}
			$updater->setContent( $slot, $content );
		}

		$rev = $updater->saveRevision(
			CommentStoreComment::newUnsavedComment( 'Update pdf template' ) );

		return $updater->getStatus();
	}

	/**
	 * @param string $pageSize
	 * @return void
	 */
	private function processStyles( $pageSize ) {
		$styles = $this->getContent( 'styles' );
		$updatedStyles = $this->updateOrientation( $styles, $pageSize );
		$this->slotData[ 'styles' ] = $updatedStyles;
	}

	/**
	 * @param array $headerData
	 * @return bool
	 */
	private function processHeader( $headerData ) {
		if ( !$headerData ) {
			$this->slotData['header'] = '';
			return true;
		}

		$doc = $this->loadHtmlDocument( $this->getContent( 'header' ) );
		$xpath = new DOMXPath( $doc );

		$tds = $this->validateAndModifyTable( $xpath, 'pdfcreator-runningheaderfix', 2 );
		if ( !$tds ) {
			$this->errors['header'] = 'pdfcreator-edit-template-values-header-error';
			return false;
		}

		$logoContent = '';
		if ( $headerData['useWikiLogo'] ) {
			$logoContent = '{{{logo}}}';
		} elseif ( $headerData['headerImage'] ) {
			$logoContent = '[[File:' . $headerData['headerImage'] . ']]';
		}

		[ $logoIndex, $titleIndex ] = $headerData['leftAlign'] ? [ 0, 1 ] : [ 1, 0 ];

		$this->replaceChildNodes(
			$tds->item( $logoIndex ),
			$this->createElementWithClass( $doc, 'div', 'pdfcreator-runningheader-logo', $logoContent )
		);

		$this->replaceChildNodes(
			$tds->item( $titleIndex ),
			$this->createElementWithClass( $doc, 'h1', 'title', $headerData['headerText'] )
		);

		$this->slotData['header'] = $this->extractHtmlBodyContent( $doc );
		return true;
	}

	/**
	 * @param DomDocument $doc
	 * @param string $tagName
	 * @param string $className
	 * @param string|null $textContent
	 * @return DomElement
	 */
	private function createElementWithClass( $doc, $tagName, $className, $textContent = null ) {
		$element = $doc->createElement( $tagName );

		if ( $className !== '' ) {
			$element->setAttribute( 'class', $className );
		}
		if ( $textContent !== null ) {
			$element->nodeValue = $textContent;
		}

		return $element;
	}

	/**
	 * @param DOMNode $parent
	 * @param DOMNode $newChild
	 * @return void
	 */
	private function replaceChildNodes( $parent, $newChild ) {
		while ( $parent->hasChildNodes() ) {
			$parent->removeChild( $parent->lastChild );
		}
		$parent->appendChild( $newChild );
	}

	/**
	 * @param array $footerData
	 * @return bool
	 */
	private function processFooter( $footerData ) {
		if ( !$footerData ) {
			$this->slotData[ 'footer' ] = '';
			return true;
		}
		$content = $this->getContent( 'footer' );
		$doc = $this->loadHtmlDocument( $content );
		$xpath = new DOMXPath( $doc );

		$tds = $this->validateAndModifyTable( $xpath, 'pdfcreator-runningfooterfix', 3 );
		if ( !$tds ) {
			$this->errors['footer'] = 'pdfcreator-edit-template-values-footer-error';
			return false;
		}

		$contents = [
			$footerData['leftContent'] ?? '',
			$footerData['middleContent'] ?? '',
			$footerData['rightContent'] ?? ''
		];
		foreach ( $tds as $index => $td ) {
			$this->replaceChildNodes(
				$td,
				$this->createElementWithClass( $doc, 'span', '', $contents[$index] )
			);
		}

		$this->slotData['footer'] = $this->extractHtmlBodyContent( $doc );
		return true;
	}

	/**
	 * @param array $introData
	 * @return bool
	 */
	private function processIntro( $introData ) {
		if ( !$introData ) {
			$this->slotData[ 'intro' ] = '';
			return true;
		}
		$doc = $this->loadHtmlDocument( $this->getContent( 'intro' ) );
		$xpath = new DOMXPath( $doc );

		$div = $this->validateAndModifyDiv( $xpath, 'pdfcreator-intro' );
		if ( !$div ) {
			$this->errors['intro'] = 'pdfcreator-edit-template-values-intro-error';
			return false;
		}

		$introTitle = $xpath->query( './/div[contains(@class, "pdfcreator-intro-title")]', $div );
		if ( $introTitle->length === 1 ) {
			$introTitle->item( 0 )->nodeValue = $introData['title'];
		}

		$introText = $xpath->query( './/div[contains(@class, "pdfcreator-intro-text")]', $div );
		if ( $introText->length === 1 ) {
			$introText->item( 0 )->nodeValue = $introData['text'];
		} else {
			if ( $introData['text'] ) {
				$text = $doc->createElement( 'div', $introData['text'] );
				$text->setAttribute( 'class', 'pdfcreator-intro-text' );
				$div->appendChild( $text );
			}
		}

		$this->slotData['intro'] = $this->extractHtmlBodyContent( $doc );
		return true;
	}

	/**
	 * @param array $outroData
	 * @return bool
	 */
	private function processOutro( $outroData ) {
		if ( !$outroData ) {
			$this->slotData[ 'outro' ] = '';
			return true;
		}
		$doc = $this->loadHtmlDocument( $this->getContent( 'outro' ) );
		$xpath = new DOMXPath( $doc );

		$div = $this->validateAndModifyDiv( $xpath, 'pdfcreator-outro' );
		if ( !$div ) {
			$this->errors['outro'] = 'pdfcreator-edit-template-values-outro-error';
			return false;
		}

		$div->nodeValue = $outroData['desc'];

		$this->slotData['outro'] = $this->extractHtmlBodyContent( $doc );
		return true;
	}

	/**
	 * @param string $content
	 * @return DOMDocument
	 */
	private function loadHtmlDocument( $content ) {
		$doc = new DOMDocument();
		$htmlText = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body>{$content}</body></html>";
		$doc->loadHTML( $htmlText );
		return $doc;
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $containerClass
	 * @param int $expectedTdCount
	 * @return DOMNodeList|false
	 */
	private function validateAndModifyTable( DOMXPath $xpath, string $containerClass, int $expectedTdCount ) {
		$div = $xpath->query( "//div[contains(@class, '{$containerClass}')]" );
		if ( $div->length !== 1 ) {
			return false;
		}

		$table = $xpath->query( ".//table", $div->item( 0 ) );
		if ( $table->length !== 1 ) {
			return false;
		}

		$tr = $xpath->query( ".//tr", $table->item( 0 ) );
		if ( $tr->length !== 1 ) {
			return false;
		}

		$tds = $xpath->query( ".//td", $tr->item( 0 ) );
		if ( $tds->length === $expectedTdCount ) {
			return $tds;
		}
		return false;
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $containerClass
	 * @return DOMElement|bool
	 */
	private function validateAndModifyDiv( DOMXPath $xpath, string $containerClass ) {
		$div = $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " ' .
			$containerClass . ' ")]' );
		return ( $div->length === 1 ) ? $div->item( 0 ) : false;
	}

	/**
	 * @param DOMDocument $doc
	 * @return void
	 */
	private function extractHtmlBodyContent( DOMDocument $doc ) {
		$htmlBody = $doc->getElementsByTagName( 'body' )->item( 0 );
		return implode( '', array_map( static fn ( $node ) => $doc->saveHTML( $node ),
			iterator_to_array( $htmlBody->childNodes ) ) );
	}

	/**
	 * @param string $slotname
	 * @return string
	 */
	private function getContent( $slotname ) {
		if ( $this->createNewTemplate ) {
			return $this->getDefaultContent( $slotname );
		}
		$slotPrefixed = $slotname;
		if ( $slotname !== 'main' ) {
			$slotPrefixed = $this->pdfCreatorUtil->templatePrefix . $slotname;
		}
		$content = $this->getSlotContent( $slotPrefixed );
		if ( !$content ) {
			return $this->getDefaultContent( $slotname );
		}
		return $content;
	}

	/**
	 * @param string $slot
	 * @return string
	 */
	private function getSlotContent( $slot ) {
		$revId = $this->pdfTemplateTitle->getLatestRevID();
		if ( $revId < 1 ) {
			return '';
		}
		$revision = $this->revisionLookup->getRevisionByTitle( $this->pdfTemplateTitle, $revId );
		if ( !$revision ) {
			return '';
		}
		$content = $revision->getContent( $slot );
		if ( !$content instanceof TextContent ) {
			return '';
		}
		return $content->getText();
	}

	/**
	 * @param string $slot
	 * @return string
	 */
	private function getDefaultContent( $slot ) {
		$file = self::$TEMPLATE_DIR . '/' . ucfirst( $slot ) . '.html';
		if ( $slot === 'intro' || $slot === 'outro' ) {
			$file = self::$TEMPLATE_DIR . '/Default/' . ucfirst( $slot ) . '.html';
		} elseif ( $slot === 'styles' ) {
			$file = self::$TEMPLATE_DIR . '/' . ucfirst( $slot ) . '.css';
		}

		return file_get_contents( $file );
	}

	/**
	 * @param string $styles
	 * @param string $orientation
	 * @return string
	 */
	private function updateOrientation( $styles, $orientation ) {
		$pageStyles = preg_replace_callback(
			'/(@page\s*{[^}]*size:\s*A4\s+(portrait|landscape);)/',
			static function ( $matches ) use ( $orientation ) {
				if ( $matches[2] === $orientation ) {
					return $matches[1];
				}
				return str_replace( $matches[2], $orientation, $matches[1] );
			},
			$styles
		);
		return preg_replace_callback(
			'/(@coverpage\s*{[^}]*size:\s*A4\s+(portrait|landscape);)/',
			static function ( $matches ) use ( $orientation ) {
				if ( $matches[2] === $orientation ) {
					return $matches[1];
				}
				return str_replace( $matches[2], $orientation, $matches[1] );
			},
			$pageStyles
		);
	}

}
