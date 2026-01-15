<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

class RendererDataTreeBuilder {

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
	 * Example return:
	 * [
	 *	'renderer' => 'panel',
	 *	'data' => [
	 *		'id' => 'my-panel',
	 *		'header' => 'My card',
	 *		'items' => [
	 *			[
	 *				'renderer' => 'literal',
	 *				'data' => [
	 *					'id' => 'my-literal',
	 *					'text' => 'Hello World!'
	 *				]
	 *			],
	 *			[
	 *				'renderer' => 'button',
	 *				'data' => [
	 *					'id' => 'my-button',
	 *					'label' => 'Hello World!'
	 *				]
	 *			]
	 *		]
	 *	],
	 * ]
	 * @param array $componentTreeNodes e.g. [
	 *   'my-panel' => [
	 *     'component' => <object>:IComponent,
	 *     'subComponents' => [
	 *       'my-literal' => [
	 *         'component' => <object>:IComponent,
	 *         'subComponents' => []
	 *       ],
	 *       'my-button' => [
	 *         'component' => <object>:IComponent,
	 *         'subComponents' => []
	 *       ]
	 *     ]
	 *   ]
	 * ];
	 * @return array
	 */
	public function getRendererDataTree( $componentTreeNodes ) {
		$templateDataTreeNodes = [];
		foreach ( $componentTreeNodes as $componentTreeNode ) {
			$templateDataTreeNodes[] = $this->getDataTreeNode( $componentTreeNode );
		}

		return $templateDataTreeNodes;
	}

	/**
	 *
	 * @param array $componentTreeNode
	 * @return array
	 */
	private function getDataTreeNode( $componentTreeNode ) {
		$subComponentTree = $componentTreeNode['subComponents'];
		$subComponentDataNodes = [];
		foreach ( $subComponentTree as $subComponentNode ) {
			$subComponentDataNodes[] = $this->getDataTreeNode( $subComponentNode );
		}

		$component = $componentTreeNode['component'];
		$rendererKey = $this->rendererFactory->getKey( $component );
		$renderer = $this->rendererFactory->getRenderer( $rendererKey );
		$templateData = $renderer->getRendererDataTreeNode( $component, $subComponentDataNodes );

		$dataNode = [
			'renderer' => $rendererKey,
			'data' => $templateData
		];

		return $dataNode;
	}
}
