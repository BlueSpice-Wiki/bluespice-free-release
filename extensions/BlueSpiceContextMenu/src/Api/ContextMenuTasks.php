<?php

namespace BlueSpice\ContextMenu\Api;

use BlueSpice\ContextMenu\IMenuItem;
use BlueSpice\ExtensionAttributeBasedRegistry;
use Exception;
use MediaWiki\Api\ApiResult;
use MediaWiki\Title\Title;
use stdClass;

class ContextMenuTasks extends \BSApiTasksBase {

	/** @var array */
	protected $aTasks = [
		'getMenuItems' => [
			'examples' => [
				[
					'title' => 'Main_page'
				]
			],
			'params' => [
				'title' => [
					'desc' => 'Valid Title value',
					'type' => 'string',
					'required' => true
				]
			]
		]
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [ 'getMenuItems' => [ 'read' ] ];
	}

	/**
	 *
	 * @param stdClass $oData
	 * @param array $aParams
	 * @return ApiResult
	 * @throws Exception
	 */
	protected function task_getMenuItems( $oData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$oResult = $this->getResult();

		if ( !isset( $oData->title ) || empty( $oData->title ) ) {
			return $oResult;
		}

		$oTitle = Title::newFromText( $oData->title );

		$itemsObjects = [];
		$itemFactories = new ExtensionAttributeBasedRegistry( 'BlueSpiceContextMenuItemFactories' );
		foreach ( $itemFactories->getAllKeys() as $itemId ) {
			$factoryCallback = $itemFactories->getValue( $itemId );
			if ( !is_callable( $factoryCallback ) ) {
				throw new Exception( "Callback for '$itemId' invalid!" );
			}
			$item = call_user_func_array( $factoryCallback, [ $oTitle ] );
			if ( $item instanceof IMenuItem === false ) {
				throw new Exception( "Callback for '$itemId' returned no IMenuItem!" );
			}
			$itemsObjects[$itemId] = $item;
		}

		$items = $this->prepareForOuput( $itemsObjects );
		$this->services->getHookContainer()->run( 'BsContextMenuGetItems', [
			&$items,
			$oTitle
		] );

		$return = $this->returnItems( $oResult, $items );
		return $return;
	}

	/**
	 *
	 * @param ApiResult &$oResult
	 * @param array $aItems
	 * @return ApiResult
	 */
	protected function returnItems( ApiResult &$oResult, $aItems ) {
		$oResult->success = true;
		$oResult->payload_count = count( $aItems );
		$oResult->payload = [ 'items' => $aItems ];
		return $oResult;
	}

	/**
	 *
	 * @return bool
	 */
	public function isWriteMode() {
		return false;
	}

	/**
	 *
	 * @param IMenuItem[] $itemsObjects
	 * @return array
	 */
	private function prepareForOuput( array $itemsObjects ) {
		$items = [];

		foreach ( $itemsObjects as $itemId => $item ) {
			if ( !$item->shouldList( $this->getContext() ) ) {
				continue;
			}
			$items[] = [
				'text' => $item->getLabelMessage()->text(),
				'href' => $item->getUrl(),
				'id' => $item->getId(),
				'icon' => $item->getIconClass(),
				'flags' => $item->getFlags(),
				'position' => $item->getPosition(),
				'primary' => $item->isPrimary(),
				'overrides' => $item->getOverride()
			];
		}

		return $items;
	}
}
