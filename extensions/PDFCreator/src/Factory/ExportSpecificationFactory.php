<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use MediaWiki\Extension\PDFCreator\Utility\ExportSpecification;

class ExportSpecificationFactory {

	/**
	 * @param string $module
	 * @param string $templateProvider
	 * @param string $target
	 * @param string $backend
	 * @param array $items
	 * @param array $config
	 * @param array $params
	 * @return ExportSpecification
	 */
	public function new(
		string $module, string $templateProvider, string $target,
		string $backend, array $items, array $config, array $params
	): ExportSpecification {
		return new ExportSpecification(
			$module, $templateProvider, $target, $backend, $items, $params, $config
		);
	}

	/**
	 * @param array $spec
	 * @return ExportSpecification
	 */
	public function createNewSpec( array $spec ): ExportSpecification {
		if ( isset( $spec['module'] ) ) {
			$module = $spec['module'];
		} else {
			$module = 'batch';
		}

		// unused
		if ( isset( $spec['template-provider'] ) ) {
			$templateProvider = $spec['template-provider'];
		} else {
			$templateProvider = 'wiki';
		}

		// unused
		if ( isset( $spec['backend'] ) ) {
			$backend = $spec['backend'];
		} else {
			$backend = 'open-html-2-pdf';
		}

		if ( isset( $spec['target'] ) ) {
			$target = $spec['target'];
		} else {
			$target = 'filesystem';
		}

		if ( isset( $spec['pages'] ) ) {
			$pages = $spec['pages'];
		} else {
			$pages = [];
		}

		if ( isset( $spec['params'] ) ) {
			$params = $spec['params'];
		} else {
			$params = [];
		}

		if ( !isset( $params['template'] ) ) {
			$params['template'] = 'StandardPDF';
		}

		if ( isset( $spec['options'] ) ) {
			$options = $spec['options'];
		} else {
			$options = [];
		}

		return new ExportSpecification(
			$module, $templateProvider, $target, $backend, $pages, $params, $options
		);
	}

	/**
	 * @param string $json
	 * @return ExportSpecification
	 */
	public function newFromJson( string $json ): ExportSpecification {
		$spec = json_decode( $json, true );

		// TODO: Error handling if $spec === null

		return $this->createNewSpec( $spec );
	}
}
