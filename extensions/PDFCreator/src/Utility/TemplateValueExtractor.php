<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMXPath;
use MediaWiki\Config\Config;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\PDFCreator\PDFCreatorUtil;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Title\Title;

class TemplateValueExtractor {

	/** @var string */
	private static $TEMPLATE_DIR;

	/** @var PDFCreatorUtil */
	private $pdfCreatorUtil;

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var Config */
	private $config;

	/** @var bool */
	private $useDefaultValues;

	/** @var Title|null */
	private $pdfTemplateTitle;

	/** @var array */
	private $templateValues;

	/** @var array */
	private $errors;

	/**
	 * @param PDFCreatorUtil $pdfCreatorUtil
	 * @param RevisionLookup $revisionLookup
	 * @param Config $config
	 */
	public function __construct( PDFCreatorUtil $pdfCreatorUtil, RevisionLookup $revisionLookup, Config $config ) {
		$this->pdfCreatorUtil = $pdfCreatorUtil;
		$this->revisionLookup = $revisionLookup;
		$this->config = $config;

		self::$TEMPLATE_DIR = realpath( __DIR__ . '/../../data/PDFTemplates' );

		$this->useDefaultValues = false;
		$this->errors = [];
		$this->templateValues = [
			'general' => [],
			'header' => [],
			'footer' => [],
			'intro' => [],
			'outro' => []
		];
	}

	/**
	 * @param string $templateName
	 * @return array
	 */
	public function getTemplateValues( string $templateName = '' ) {
		if ( $templateName !== '' ) {
			$this->pdfTemplateTitle = $this->pdfCreatorUtil->createPDFTemplateTitle( $templateName );
			if ( !$this->pdfTemplateTitle->exists() ) {
				$this->useDefaultValues = true;
				$this->pdfTemplateTitle = null;
			} else {
				$this->templateValues['general']['template'] = $templateName;
			}
		} else {
			$this->useDefaultValues = true;
		}

		$this->getOptions();
		$this->getDesc();
		$this->getSize();

		$headerValues = $this->getHeaderValues();
		$footerValues = $this->getFooterValues();
		$introValues = $this->getIntroValues();
		$outroValues = $this->getOutroValues();
		if ( !$headerValues || !$footerValues || !$introValues || !$outroValues ) {
			return [ 'errors' => $this->errors ];
		}

		return $this->templateValues;
	}

	/**
	 * @return bool
	 */
	private function getHeaderValues() {
		$content = $this->getContent( 'header' );
		if ( $content === '' ) {
			$this->templateValues['header'] = [];
			return true;
		}

		$doc = $this->loadHtmlDocument( $content );
		$xpath = new DOMXPath( $doc );

		// Check if runningheaderfix is set
		$tds = $this->validateAndModifyTable( $xpath, 'pdfcreator-runningheaderfix', 2 );
		if ( !$tds ) {
			$this->errors['header'] = 'pdfcreator-edit-template-values-header-error';
			return false;
		}

		$leftContent = trim( $tds->item( 0 )->nodeValue );
		$rightContent = trim( $tds->item( 1 )->nodeValue );

		$leftHasLogo = $xpath->query( './/div[contains(@class, "pdfcreator-runningheader-logo")]',
			$tds->item( 0 ) )->length > 0;

		$logoUrl = $this->config->get( 'Logo' );
		$headerValues = [
			'leftAlign' => $leftHasLogo,
			'logoPath' => $logoUrl
		];
		if ( $leftHasLogo ) {
			$useWikiLogo = false;
			if ( $leftContent === '{{{logo}}}' ) {
				$useWikiLogo = true;
			}
			if ( !$useWikiLogo ) {
				$imageName = $this->getFileName( $leftContent );
				$headerValues['logoName'] = $imageName;
			}
			$headerValues['headerText'] = $rightContent;
		} else {
			$useWikiLogo = false;
			if ( $rightContent === '{{{logo}}}' ) {
				$useWikiLogo = true;
			}
			if ( !$useWikiLogo ) {
				$imageName = $this->getFileName( $rightContent );
				$headerValues['logoName'] = $imageName;
			}
			$headerValues['headerText'] = $leftContent;
		}
		$headerValues['useWikiLogo'] = $useWikiLogo;
		$this->templateValues['header'] = $headerValues;
		return true;
	}

	/**
	 * @param string $filelink
	 * @return string
	 */
	private function getFileName( $filelink ) {
		if ( preg_match( '/\[\[File:([^|\]]+)\]\]/', $filelink, $matches ) ) {
			return $matches[1];
		}
		return '';
	}

	/**
	 * @return bool
	 */
	private function getFooterValues() {
		$content = $this->getContent( 'footer' );
		if ( $content === '' ) {
			$this->templateValues['footer'] = [];
			return true;
		}

		$doc = $this->loadHtmlDocument( $content );
		$xpath = new DOMXPath( $doc );

		// Check if runningfooterfix is set
		$tds = $this->validateAndModifyTable( $xpath, 'pdfcreator-runningfooterfix', 3 );
		if ( !$tds ) {
			$this->errors['footer'] = 'pdfcreator-edit-template-values-footer-error';
			return false;
		}

		$leftContent = trim( $tds->item( 0 )->nodeValue );
		$middleContent = trim( $tds->item( 1 )->nodeValue );
		$rightContent = trim( $tds->item( 2 )->nodeValue );

		$footerValues = [
			'leftContent' => $leftContent,
			'middleContent' => $middleContent,
			'rightContent' => $rightContent
		];

		$this->templateValues['footer'] = $footerValues;
		return true;
	}

	/**
	 * @return bool
	 */
	private function getIntroValues() {
		$content = $this->getContent( 'intro' );
		if ( $content === '' ) {
			$this->templateValues['intro'] = [];
			return true;
		}

		$doc = $this->loadHtmlDocument( $content );
		$xpath = new DOMXPath( $doc );

		// Check if pdfcreator-intro is set
		$div = $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " pdfcreator-intro ")]' );
		if ( $div->length !== 1 ) {
			$this->errors['intro'] = 'pdfcreator-edit-template-values-intro-error';
			return false;
		}

		// Check if title is set
		$introValues = [];
		$introTitleCnt = $xpath->query( './/div[contains(@class, "pdfcreator-intro-title")]', $div->item( 0 ) );
		if ( $introTitleCnt->length === 1 ) {
			$introTitle = trim( $introTitleCnt->item( 0 )->nodeValue );
			$introValues['introTitle'] = $introTitle;
		}

		$introTextCnt = $xpath->query( './/div[contains(@class, "pdfcreator-intro-text")]', $div->item( 0 ) );
		if ( $introTextCnt->length === 1 ) {
			$introText = trim( $introTextCnt->item( 0 )->nodeValue );
			$introValues['coverText'] = $introText;
		}

		if ( !$this->templateValues['general']['options']['coverBackground'] ) {
			$introValues['useDefaultBg'] = true;
		} else {
			$introValues['useDefaultBg'] = false;
			$introValues['coverBackground'] = $this->templateValues['general']['options']['coverBackground'];
		}

		$this->templateValues['intro'] = $introValues;
		return true;
	}

	/**
	 * @return bool
	 */
	private function getOutroValues() {
		$content = $this->getContent( 'outro' );
		if ( $content === '' ) {
			$this->templateValues['outro'] = [];
			return true;
		}

		$doc = $this->loadHtmlDocument( $content );
		$xpath = new DOMXPath( $doc );

		$div = $xpath->query( '//div[contains(@class, "pdfcreator-outro")]' );
		if ( $div->length !== 1 ) {
			$this->errors['outro'] = 'pdfcreator-edit-template-values-outro-error';
			return false;
		}
		$outroValues = [];
		$outroText = trim( $div->item( 0 )->nodeValue );
		if ( $outroText !== '' ) {
			$outroValues['desc'] = $outroText;
		}
		$this->templateValues['outro'] = $outroValues;
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
	 * @return DomNodeList|bool
	 */
	private function validateAndModifyTable( DOMXPath $xpath, string $containerClass, int $expectedTdCount ) {
		$div = $xpath->query( "//div[contains(@class, '{$containerClass}')]" );
		if ( !$div || $div->length !== 1 ) {
			return false;
		}

		$table = $xpath->query( ".//table", $div->item( 0 ) );
		if ( !$table || $table->length !== 1 ) {
			return false;
		}

		$tr = $xpath->query( ".//tr", $table->item( 0 ) );
		if ( !$tr || $tr->length !== 1 ) {
			return false;
		}

		$tds = $xpath->query( ".//td", $tr->item( 0 ) );
		if ( $tds->length === $expectedTdCount ) {
			return $tds;
		}
		return false;
	}

	/**
	 * @param string $slotname
	 * @return string
	 */
	private function getContent( $slotname ) {
		if ( $this->useDefaultValues ) {
			return $this->getDefaultContent( $slotname );
		}
		if ( $slotname !== 'main' ) {
			$slotname = $this->pdfCreatorUtil->templatePrefix . $slotname;
		}
		return $this->getSlotContent( $slotname );
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

		$content = file_get_contents( $file );
		return $content;
	}

	/**
	 * @return void
	 */
	private function getOptions() {
		$optionsContent = '';
		if ( $this->useDefaultValues ) {
			$optionsContent = file_get_contents( self::$TEMPLATE_DIR . '/Options.json' );
		} else {
			$optionsContent = $this->getSlotContent( $this->pdfCreatorUtil->templatePrefix . 'options' );
		}

		$options = json_decode( $optionsContent, true );
		$this->templateValues['general']['options'] = $options;
	}

	/**
	 * @return void
	 */
	private function getDesc() {
		$desc = $this->getContent( 'main' );
		if ( $desc ) {
			$this->templateValues['general']['desc'] = $desc;
		}
	}

	/**
	 * @return void
	 */
	private function getSize() {
		if ( $this->useDefaultValues ) {
			$this->templateValues['general']['size'] = 'portrait';
			return;
		}
		$styleContent = $this->getSlotContent( $this->pdfCreatorUtil->templatePrefix . 'styles' );
		$size = $this->extractOrientation( $styleContent );
		$this->templateValues['general']['size'] = $size;
	}

	/**
	 * @param string $styles
	 * @return void
	 */
	private function extractOrientation( $styles ) {
		if ( preg_match( '/@page\s*{[^}]*size:\s*A4\s+(portrait|landscape);/', $styles, $matches ) ) {
			return $matches[1];
		}

		return 'portrait';
	}

}
