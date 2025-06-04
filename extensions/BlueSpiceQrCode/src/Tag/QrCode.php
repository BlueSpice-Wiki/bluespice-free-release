<?php

namespace BlueSpice\QrCode\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\GenericHandler;
use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\Tag\Tag;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;

class QrCode extends Tag {

	public const PARAM_PAGE = 'page';
	public const PARAM_QUERY = 'query';
	public const PARAM_DESC = 'desc';
	public const PARAM_SIZE = 'size';

	/**
	 * @inheritDoc
	 */
	public function getTagNames() {
		return [ 'qrcode' ];
	}

	/**
	 *
	 * @return NoWiki
	 */
	public function getMarkerType() {
		return new NoWiki();
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParsedInput() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	public function getContainerElementName() {
		return GenericHandler::TAG_DIV;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
	PPFrame $frame ) {
		$context = RequestContext::getMain();
		$services = MediaWikiServices::getInstance();
		$titleFactory = $services->getTitleFactory();
		/** @var DynamicFileDispatcherFactory $dfdFactory */
		$dfdFactory = $services->getService( 'MWStake.DynamicFileDispatcher.Factory' );
		return new QrCodeHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			$context,
			$titleFactory,
			$dfdFactory
		);
	}

	/**
	 *
	 * @return ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_PAGE,
				''
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_QUERY,
				''
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_DESC,
				''
			),
			new ParamDefinition(
				ParamType::INTEGER,
				static::PARAM_SIZE,
				120
			)
		];
	}

}
