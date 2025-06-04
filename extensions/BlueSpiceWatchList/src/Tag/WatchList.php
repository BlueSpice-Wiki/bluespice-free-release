<?php

namespace BlueSpice\WatchList\Tag;

use BlueSpice\ParamProcessor\IParamDefinition;
use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\Tag\Tag;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use ValueValidators\RangeValidator;
use ValueValidators\StringValidator;

class WatchList extends Tag {
	public const ATTR_COUNT = 'count';
	public const ATTR_MAX_TITLE_LENGTH = 'maxtitlelength';
	public const ATTR_ORDER = 'order';
	public const ATTR_ORDER_PAGE_NAME = 'pagename';
	public const ATTR_ORDER_TIME = 'time';

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
		return new WatchListHandler(
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
			'bs:watchlist',
			'watchlist',
		];
	}

	/**
	 * @return IParamDefinition[]
	 */
	public function getArgsDefinitions() {
		$count = new ParamDefinition(
			ParamType::INTEGER,
			static::ATTR_COUNT,
			5
		);
		$titleLength = new ParamDefinition(
			ParamType::INTEGER,
			static::ATTR_MAX_TITLE_LENGTH,
			20
		);

		$order = new ParamDefinition(
			ParamType::STRING,
			static::ATTR_ORDER,
			static::ATTR_ORDER_TIME
		);

		$validator = new RangeValidator();
		$validator->setRange( 1, 1000 );
		$count->setValueValidator( $validator );

		$validator = new RangeValidator();
		$validator->setRange( 5, 500 );
		$titleLength->setValueValidator( $validator );

		$validator = new StringValidator();
		$validator->setOptions( [ 'values' => [
			static::ATTR_ORDER_PAGE_NAME,
			static::ATTR_ORDER_TIME
		] ] );
		$order->setValueValidator( $validator );
		return [
			$count,
			$titleLength,
			$order
		];
	}

}
