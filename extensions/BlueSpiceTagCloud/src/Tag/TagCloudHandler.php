<?php

namespace BlueSpice\TagCloud\Tag;

use BlueSpice\TagCloud\Context;
use BlueSpice\TagCloud\Factory;
use BlueSpice\TagCloud\Renderer;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class TagCloudHandler implements ITagHandler {

	public function __construct(
		private readonly Factory $factory,
		private readonly UserFactory $userFactory,
		private readonly ConfigFactory $configFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		if ( isset( $params['type'] ) && $params['type'] !== '' ) {
			$storeType = $params['type'];
		} elseif ( isset( $params['store'] ) ) {
			$storeType = $params['store'];
		} else {
			$storeType = $this->factory->getDefaultStoreType();
		}

		$user = $this->userFactory->newFromUserIdentity( $parser->getUserIdentity() );
		$config = $this->configFactory->makeConfig( 'bsg' );

		$context = new Context(
			RequestContext::getMain(),
			$config,
			$user
		);
		$store = $this->factory->getStore( $storeType, $context );

		$readerParams = $store->makeReaderParams( $params );

		$result = $store->getReader()->read( $readerParams );

		if ( isset( $params['viewtype'] ) && $params['viewtype'] !== '' ) {
			$rendererType = $params['viewtype'];
		} elseif ( isset( $params['renderer'] ) && $params['renderer'] !== '' ) {
			$rendererType = $params['renderer'];
		} else {
			$rendererType = $this->factory->getDefaultRendererType();
		}

		$params = array_merge( $params, [
			Renderer::PARAM_RESULT => $result,
			Renderer::PARAM_CONTEXT => $context,
			Renderer::PARAM_STORE => $storeType,
			Renderer::PARAM_RENDERER => $rendererType,
		] );

		$renderer = $this->factory->getRenderer(
			$rendererType,
			$params
		);
		return $renderer->render();
	}
}
