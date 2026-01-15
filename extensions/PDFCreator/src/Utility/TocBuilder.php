<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Message\Message;
use MediaWiki\Title\TitleFactory;

class TocBuilder {

	/** @var TitleFactory */
	protected $titleFactory;

	/**
	 *
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param array $pages
	 * @param bool $embedPageToc
	 * @return array
	 */
	public function execute( array $pages, bool $embedPageToc = false ): array {
		$tocLabel = $this->getPageLabelMsg();

		$container = $this->getTocDOMContainer( $pages, $embedPageToc );
		$dom = $container->ownerDocument;
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$body->appendChild( $container );

		$tocPage = new ExportPage(
			'toc',
			$dom,
			$tocLabel->text()
		);

		array_unshift( $pages, $tocPage );

		return $pages;
	}

	/**
	 * @param array $pages
	 * @param bool $embedPageToc
	 * @return string
	 */
	public function getHtml( array $pages, bool $embedPageToc = false ): string {
		$container = $this->getTocDOMContainer( $pages, $embedPageToc );
		return $container->ownerDocument->saveHTML( $container );
	}

	/**
	 * @param array $pages
	 * @param bool $embedPageToc
	 * @return DOMElement
	 */
	private function getTocDOMContainer( array $pages, bool $embedPageToc = false ): DOMElement {
		$dom = new DOMDocument();
		$dom->loadXML( PDFCreator::HTML_STUB );

		$container = $dom->createElement( 'div' );
		$container->setAttribute( 'class', 'pdfcreator-page pdfcreator-type-toc' );

		$ul = $dom->createElement( 'ul' );
		$ul->setAttribute( 'class', 'toc' );

		$curLevel = 1;

		for ( $index = 0; $index < count( $pages ); $index++ ) {
			$page = $pages[$index];
			if ( $page->getType() === 'intro' || $page->getType() === 'outro' ) {
				continue;
			}

			$li = $dom->createElement( 'li' );
			$li->setAttribute( 'class', 'toclevel-' . (string)$curLevel );

			if ( $page->getPrefixedDBKey() ) {
				$this->setNewClass( $li, $page->getPrefixedDBKey() );
			}

			$a = $dom->createElement( 'a' );
			$a->setAttribute( 'class', 'toc-link' );
			$a->setAttribute( 'href', '#' . $page->getUniqueId() );

			$tocNumber = $a->appendChild(
				$dom->createElement( 'span' )
			);
			$tocNumber->setAttribute( 'class', 'tocnumber' );
			$tocNumber->appendChild( $dom->createTextNode( $index + 1 . '.' ) );

			$tocText = $a->appendChild(
				$dom->createElement( 'span' )
			);
			$tocText->setAttribute( 'class', 'toctext' );

			/** @var DOMDocument */
			$pageDom = $page->getDOMDocument();

			$firstHeading = $pageDom->getElementById( $page->getUniqueId() );
			if ( $firstHeading instanceof DOMNode ) {
				$text = $firstHeading->nodeValue;
			} else {
				$text = $page->getLabel();
			}
			$tocText->appendChild( $dom->createTextNode( ' ' . $text ) );

			$li->appendChild( $a );

			if ( $embedPageToc ) {
				$this->appendPageToc( $li, $index + 1, $curLevel, $page->getDOMDocument() );
			}

			$ul->appendChild( $li );
		}

		$container->appendChild( $ul );

		return $container;
	}

	/**
	 *
	 * @param DOMElement $li
	 * @param string $dbkey
	 * @return void
	 */
	protected function setNewClass( $li, $dbkey ) {
		$title = $this->titleFactory->newFromDBkey( $dbkey );
		if ( !$title->exists() ) {
			$classes = $li->getAttribute( 'class' );
			$classes .= ' toc-new';
			$li->setAttribute( 'class', $classes );
		}
	}

	/**
	 * @return Message
	 */
	protected function getPageLabelMsg(): Message {
		return Message::newFromKey( 'pdfcreator-toc-page-label' );
	}

	/**
	 * @param DOMElement $li
	 * @param int $number
	 * @param int $curLevel
	 * @param DOMDocument $dom
	 * @return void
	 */
	private function appendPageToc( DOMElement $li, int $number, int $curLevel, DOMDocument $dom ): void {
		$xpath = new DOMXPath( $dom );
		$tocList = $xpath->query(
			'//div[contains(@class, "toc")]/ul',
			$dom
		);
		if ( $tocList instanceof DOMNodeList ) {
			$pageToc = $tocList->item( 0 );
			if ( $pageToc !== null ) {
				$pageTocLi = $pageToc->getElementsByTagName( 'li' );
				foreach ( $pageTocLi as $ptLi ) {
					if ( $ptLi instanceof DOMElement === false ) {
						continue;
					}
					if ( !$ptLi->hasAttribute( 'class' ) ) {
						continue;
					}
					$ptLiClass = $ptLi->getAttribute( 'class' );
					$ptLiClass = preg_replace_callback( '#toclevel-(\d)#', static function ( $match ) use ( $curLevel )
					{
						$level = $curLevel + (int)$match[1];
						return 'toclevel-' . (string)$level;
					}, $ptLiClass );
					$ptLi->setAttribute( 'class', $ptLiClass );
				}

				$node = $li->ownerDocument->importNode( $pageToc, true );
				$class = $node->getAttribute( 'class' );
				$class .= ( strlen( $class ) === 0 ) ? 'pdfcreator-wiki-toc' : ' pdfcreator-wiki-toc';
				$node->setAttribute( 'class', $class );
				$spans = $node->getElementsByTagName( 'span' );
				foreach ( $spans as $span ) {
					if ( $span instanceof DOMElement === false ) {
						continue;
					}
					if ( !$span->hasAttribute( 'class' ) ) {
						continue;
					}
					$classes = explode( ' ', $span->getAttribute( 'class' ) );
					if ( !in_array( 'tocnumber', $classes ) ) {
						continue;
					}
					$nodeText = $span->nodeValue;
					$span->nodeValue = "$number.$nodeText";
				}
				$li->appendChild( $node );
			}
		}
	}
}
