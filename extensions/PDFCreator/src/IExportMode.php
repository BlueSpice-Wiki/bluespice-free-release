<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Title\Title;

interface IExportMode {

	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @return string
	 */
	public function getLabel(): string;

	/**
	 * @return array
	 */
	public function getRLModules(): array;

	/**
	 * @param string $format
	 * @return bool
	 */
	public function applies( $format ): bool;

	/**
	 * @param Title $title
	 * @param array $data
	 * @return array
	 */
	public function getExportPages( $title, $data ): array;

	/**
	 * @param Title $title
	 * @return bool
	 */
	public function isRelevantExportMode( $title ): bool;

	/**
	 * @return string
	 */
	public function getDefaultTemplate(): string;

}
