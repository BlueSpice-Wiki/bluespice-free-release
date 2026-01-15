<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use HtmlArmor;

class RendererDataTreeRenderer {

	/**
	 *
	 * @var ComponentRendererFactory
	 */
	private $rendererFactory = null;

	/**
	 *
	 * @param ComponentRendererFactory $rendererFactory
	 */
	public function __construct( $rendererFactory ) {
		$this->rendererFactory = $rendererFactory;
	}

	/**
	 *
	 * @param array $rendererDataTreeNodes Data tree as created by
	 * `RendererDataTreeBuilder::getRendererDataTree`
	 * @return string The finished HTML
	 */
	public function getHtml( $rendererDataTreeNodes ) {
		$html = '';
		foreach ( $rendererDataTreeNodes as $rendererDataTreeNode ) {
			$html .= $this->makeHtmlFromRendererDataTreeNode( $rendererDataTreeNode );
		}
		return $html;
	}

	/**
	 *
	 * @param array $dataTreeNode
	 * @return string
	 */
	private function makeHtmlFromRendererDataTreeNode( $dataTreeNode ) {
		$html = '';
		$renderKey = $dataTreeNode['renderer'];
		$renderer = $this->rendererFactory->getRenderer( $renderKey );
		foreach ( $dataTreeNode['data'] as $dataKey => $dataValue ) {
			if ( !is_array( $dataValue ) ) {
				continue;
			}
			$subComponentHtml = '';
			foreach ( $dataValue as $index => $item ) {
				if ( isset( $item['renderer'] ) ) {
					$subComponentHtml .= $this->makeHtmlFromRendererDataTreeNode( $item );
				}
			}
			if ( !empty( $subComponentHtml ) ) {
				$dataTreeNode['data'][$dataKey] = new HtmlArmor( $subComponentHtml );
			}
		}

		$html = $renderer->getHtml( $dataTreeNode['data'] );
		return $html;
	}

}
