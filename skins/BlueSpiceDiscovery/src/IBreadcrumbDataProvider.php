<?php

namespace BlueSpice\Discovery;

use MediaWiki\Title\Title;

interface IBreadcrumbDataProvider {

	/**
	 * @param Title $title
	 * @return Title
	 */
	public function getRelevantTitle( $title ): Title;

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getNodes( Title $title ): array;

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getLabels( Title $title ): array;

	/**
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function applies( Title $title ): bool;

	/**
	 * @param array $node
	 * @return bool
	 */
	public function isSelfLink( $node ): bool;
}
