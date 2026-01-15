<?php

namespace BlueSpice\ExtendedStatistics\Tag;

use BlueSpice\ParamProcessor\IParamDefinition;
use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\Tag\Tag;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class Progress extends Tag {
	public const ATTR_BASE_COUNT = 'basecount';
	public const ATTR_BASE_ITEM = 'baseitem';
	public const ATTR_PROGRESS_ITEM = 'progressitem';
	public const ATTR_WIDTH = 'width';

	/**
	 *
	 * @return bool
	 */
	public function needsParsedInput() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParseArgs() {
		return false;
	}

	/**
	 *
	 * @return MarkerType
	 */
	public function getMarkerType() {
		return new NoWiki();
	}

	/**
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return true;
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return WatchlistHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new ProgressHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'bs:statistics:progress',
		];
	}

	/**
	 * @return IParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::INTEGER,
				static::ATTR_BASE_COUNT,
				100
			),
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_BASE_ITEM,
				''
			),
			new ParamDefinition(
				ParamType::STRING,
				static::ATTR_PROGRESS_ITEM,
				'OK'
			),
			new ParamDefinition(
				ParamType::INTEGER,
				static::ATTR_WIDTH,
				100
			)
		];
	}

}
