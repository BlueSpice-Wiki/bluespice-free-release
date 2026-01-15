<?php

namespace BlueSpice\UserSidebar;

use MediaWiki\Content\TextContent;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class Parser {
	/**
	 *
	 * @var array[]
	 */
	protected $parsed = [];

	/**
	 *
	 * @var WidgetFactory
	 */
	protected $widgetFactory = null;

	/**
	 *
	 * @var TitleFactory
	 */
	protected $titleFactory = null;

	/**
	 *
	 * @param WidgetFactory $widgetFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( WidgetFactory $widgetFactory, TitleFactory $titleFactory ) {
		$this->widgetFactory = $widgetFactory;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param Title $title
	 * @param IContextSource $context
	 * @return array
	 */
	public function getSidebarItems( Title $title, IContextSource $context ): array {
		if ( isset( $this->parsed[$title->getFullText()] ) ) {
			return $this->parsed[$title->getFullText()];
		}
		$this->parsed[$title->getFullText()] = $sidebarItems = [];
		if ( !$title->exists() ) {
			foreach ( $this->widgetFactory->getAll( $context ) as $key => $widget ) {
				$sidebarItems[$key] = [];
			}
		} else {
			$content = $this->getContent( $title );
			foreach ( $this->parse( $content, $context ) as $section => $links ) {
				$sidebarItems[$section] = $links;
			}
		}

		foreach ( $sidebarItems as $section => $links ) {
			$widget = $this->widgetFactory->get( $section, $context );
			if ( $widget && empty( $links ) ) {
				$section = $widget->getHeaderMessage()->text();
				$links = $widget->getLinks();
			}

			$this->parsed[$title->getFullText()][$section] = $links;
		}

		return $this->parsed[$title->getFullText()];
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	protected function getContent( Title $title ) {
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		$contentObj = $wikiPage->getContent();
		$content = ( $contentObj instanceof TextContent ) ? $contentObj->getText() : '';
		return preg_replace( '#<noinclude>.*?<\/noinclude>#si', '', $content );
	}

	/**
	 * @param string $content
	 * @param IContextSource $context
	 * @return array
	 */
	protected function parse( $content, IContextSource $context ) {
		$sidebarItems = [];
		$textLines = explode( "\n", $content );
		$section = '';
		foreach ( $textLines as $line ) {
			$depth = 0;
			$isIndentCharacter = true;
			do {
				if ( isset( $line[$depth] ) && $line[$depth] == '*' ) {
					$depth++;
				} else {
					$isIndentCharacter = false;
				}
			} while ( $isIndentCharacter );

			$line = trim( substr( $line, $depth ) );
			if ( empty( $line ) ) {
				continue;
			}
			if ( $depth === 1 ) {
				$bits = explode( '|', $line );
				$name = array_shift( $bits );
				$params = [];
				foreach ( $bits as $bit ) {
					$paramBits = explode( '=', $bit );
					$paramName = array_shift( $paramBits );
					$params[$paramName] = !empty( $paramBits ) ? $paramBits[0] : '';
				}
				$instance = $this->widgetFactory->get( $name, $context, $params );
				if ( $instance ) {
					$sidebarItems[$instance->getHeaderMessage()->text()]
						= $instance->getLinks();
					$section = '';
					continue;
				}
				$section = $line;
				continue;
			}

			if ( substr( $line, 0, 2 ) === '[[' ) {
				$sidebarItems[$section][] = $this->parseInternalLink( $line );
				continue;
			}
			if ( substr( $line, 0, 1 ) === '[' ) {
				$sidebarItems[$section][] = $this->parseExternalLink( $line );
				continue;
			}
			// Its a link without square brackets
			if ( strpos( $line, 'http' ) === 0 || strpos( $line, 'https' ) === 0 ) {
				$sidebarItems[$section][] = $this->parseExternalLink( $line );
				continue;
			}
			$sidebarItems[$section][] = $this->parseInternalLink( $line );
		}

		return $sidebarItems;
	}

	/**
	 *
	 * @param string $line
	 * @return array
	 */
	protected function parseInternalLink( $line ) {
		$item = [];
		$line = ltrim( $line, '[[' );
		$line = rtrim( $line, ']]' );

		$elements = explode( '|', $line );
		$item['classes'] = ' bs-usersidebar-internal ';

		$title = $this->titleFactory->newFromText( $elements[0] );
		if ( !$title ) {
			return [];
		}

		$item['href'] = $title->getLocalURL();
		if ( $title->hasFragment() ) {
			$item['href'] .= $title->getFragmentForURL();
		}

		if ( isset( $elements[1] ) ) {
			$item['text'] = $elements[1];
			$item['title'] = $elements[1];
		} else {
			$item['text'] = $title->getText();
			$item['title'] = $title->getText();
		}

		return $item;
	}

	/**
	 *
	 * @param string $line
	 * @return array|false
	 */
	protected function parseExternalLink( $line ) {
		$item = [];
		if ( strpos( $line, '[' ) === 0 ) {
			$line = ltrim( $line, '[' );
			$line = rtrim( $line, ']' );
			$elements = explode( ' ', $line );
		} else {
			$elements = explode( '|', $line );
		}

		$item['classes'] = ' bs-usersidebar-external ';
		$item['href'] = $elements[0];

		array_shift( $elements );
		if ( empty( $elements ) ) {
			return false;
		}

		$element = implode( ' ', $elements );
		$item['text'] = $element;
		$item['title'] = $element;

		return $item;
	}
}
