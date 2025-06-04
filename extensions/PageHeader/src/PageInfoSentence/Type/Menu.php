<?php

namespace PageHeader\PageInfoSentence\Type;

use MediaWiki\Html\Html;
use PageHeader\IPageInfo;
use PageHeader\PageInfoSentence\Type;

class Menu extends Type {

	/**
	 *
	 * @param IPageInfo $pageInfo
	 * @return string
	 */
	public function build( IPageInfo $pageInfo ): string {
		$html = '';

		$html .= Html::openElement( 'span', [ 'class' => 'dropdown' ] );

		if ( $pageInfo->getUrl() !== '' ) {
			$html .= Html::element( 'a', array_merge( [
						'class' => $pageInfo->getHtmlClass(),
						'href' => $pageInfo->getUrl(),
						'id' => $pageInfo->getHtmlId()
					],
					$this->makeHtmlDataArray( $pageInfo->getHtmlDataAttribs() )
				),
				$pageInfo->getLabelMessage()->text()
			);
		} else {
			$html .= Html::element( 'span', array_merge( [
						'class' => $pageInfo->getHtmlClass(),
						'id' => $pageInfo->getHtmlId()
					],
					$this->makeHtmlDataArray( $pageInfo->getHtmlDataAttribs() )
				),
				$pageInfo->getLabelMessage()->text()
			);
		}

		$legacyMenu = $this->mayHasLegacyMenu( $pageInfo );
		if ( !empty( $legacyMenu ) ) {
			$html .= $legacyMenu;
			$html .= Html::closeElement( 'span' );
			return $html;
		}

		// TODO: $pageInfo->getTypeData() build components from array like a list of links
		return $html;
	}

	/**
	 *
	 * @param IPageInfo $pageInfo
	 * @return string
	 */
	private function mayHasLegacyMenu( IPageInfo $pageInfo ): string {
		if ( empty( $pageInfo->getMenu() ) ) {
			return '';
		}
		$html = Html::openElement( 'a', [
			'class' => ' dropdown-toggle',
			'href' => '#',
			'data-toggle' => 'dropdown',
			'data-bs-toggle' => "dropdown",
			'aria-expanded' => 'false',
			'title' => $pageInfo->getTooltipMessage()->text()
		] );
		$html .= Html::closeElement( 'a' );

		$html .= Html::openElement( 'div', [ 'class' => 'dropdown-menu mws-dropdown-secondary' ] );
		$html .= $pageInfo->getMenu();
		$html .= Html::closeElement( 'div' );
		return $html;
	}

}
