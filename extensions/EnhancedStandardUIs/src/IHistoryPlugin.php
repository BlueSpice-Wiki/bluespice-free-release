<?php

namespace MediaWiki\Extension\EnhancedStandardUIs;

interface IHistoryPlugin {

	/**
	 *
	 * @param EnhancedHistoryAction $historyAction
	 * @return array
	 */
	public function getRLModules( $historyAction ): array;

	/**
	 *
	 * @param EnhancedHistoryAction $historyAction
	 * @param array &$entry
	 * @param array &$attribs
	 * @param array &$classes
	 * @return void
	 */
	public function ammendRow( $historyAction, &$entry, &$attribs, &$classes );
}
