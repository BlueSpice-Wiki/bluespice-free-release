<?php
namespace BlueSpice\PageAssignments;

use MWStake\MediaWiki\Component\DataStore\IStore;

interface IAssignable {

	/**
	 * @return string
	 */
	public function getType();

	/**
	 * @return IStore
	 */
	public function getStore();

	/**
	 * @return string
	 */
	public function getAssignmentClass();

	/**
	 * @return string
	 */
	public function getRendererKey();

	/**
	 * @return string
	 */
	public function getTypeMessageKey();
}
