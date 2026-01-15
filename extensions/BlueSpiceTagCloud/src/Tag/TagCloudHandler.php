<?php

namespace BlueSpice\TagCloud\Tag;

use BlueSpice\Tag\Handler;
use BlueSpice\TagCloud\Context;
use BlueSpice\TagCloud\Renderer;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\User\UserFactory;

class TagCloudHandler extends Handler {

	/**
	 * @var UserFactory
	 */
	private $userFactory = null;

	/**
	 * @var ConfigFactory
	 */
	private $configFactory = null;

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param UserFactory $userFactory
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( $input, array $args, Parser $parser,
		 PPFrame $frame, UserFactory $userFactory, ConfigFactory $configFactory ) {
		parent::__construct( $input, $args, $parser, $frame );
		$this->userFactory = $userFactory;
		$this->configFactory = $configFactory;
	}

	public function handle() {
		$storeType = '';
		// backwards compatibility
		if ( isset( $this->processedArgs['type'] ) ) {
			$storeType = $this->processedArgs['type'];
		} elseif ( isset( $this->processedArgs['store'] ) ) {
			$storeType = $this->processedArgs['store'];
		} else {
			$storeType = $this->getFactory()->getDefaultStoreType();
		}

		$user = $this->userFactory->newFromUserIdentity( $this->parser->getUserIdentity() );
		$config = $this->configFactory->makeConfig( 'bsg' );

		$context = new Context(
			RequestContext::getMain(),
			$config,
			$user
		);
		$store = $this->getFactory()->getStore( $storeType, $context );

		$readerParams = $store->makeReaderParams(
			$this->processedArgs
		);

		$result = $store->getReader()->read( $readerParams );

		$rendererType = '';
		// backwards compatibility
		if ( isset( $this->processedArgs['viewtype'] ) ) {
			$rendererType = $this->processedArgs['viewtype'];
		} elseif ( isset( $this->processedArgs['renderer'] ) ) {
			$rendererType = $this->processedArgs['renderer'];
		} else {
			$rendererType = $this->getFactory()->getDefaultRendererType();
		}

		$params = array_merge( $this->processedArgs, [
			Renderer::PARAM_RESULT => $result,
			Renderer::PARAM_CONTEXT => $context,
			Renderer::PARAM_STORE => $storeType,
			Renderer::PARAM_RENDERER => $rendererType,
		] );

		$renderer = $this->getFactory()->getRenderer(
			$rendererType,
			$params
		);
		return $renderer->render();
	}

	/**
	 *
	 * @return \BlueSpice\TagCloud\Factory
	 */
	protected function getFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSTagCloudFactory' );
	}
}
