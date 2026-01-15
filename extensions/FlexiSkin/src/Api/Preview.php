<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use MediaWiki\Api\ApiUsageException;
use MediaWiki\Extension\FlexiSkin\FlexiSkin;
use MediaWiki\Extension\FlexiSkin\FlexiSkinManager;
use MediaWiki\Extension\FlexiSkin\IFlexiSkinSubscriber;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;

class Preview extends FlexiSkinApiBase {
	public function execute() {
		$this->returnResult(
			$this->getLoadData()
		);
	}

	/** @inheritDoc */
	protected function getAllowedParams() {
		return [
			'config' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
				static::PARAM_HELP_MSG => 'apihelp-flexiskin-preview-param-config',
			]
		];
	}

	/**
	 * @param array $data
	 */
	protected function returnResult( $data ) {
		$result = $this->getResult();
		$result->addValue( null, 'loadData', $data );
	}

	/**
	 * @return array
	 * @throws ApiUsageException
	 */
	private function getLoadData() {
		$this->checkPermissions();

		$vars = [];
		$config = FormatJson::decode( $this->getParameter( 'config' ), 1 );
		$flexiSkin = new FlexiSkin( null, 'preview', $config );

		/** @var FlexiSkinManager $flexiSkinManager */
		$flexiSkinManager = MediaWikiServices::getInstance()->get( 'FlexiSkinManager' );
		foreach ( $flexiSkinManager->getPlugins() as $pluginKey => $plugin ) {
			$vars += $plugin->getLessVars( $flexiSkin );
		}

		$subscribingModules = [];
		/**
		 * @var string $subKey
		 * @var IFlexiSkinSubscriber $subscriber
		 */
		foreach ( $flexiSkinManager->getSubscribers() as $subKey => $subscriber ) {
			$subscribingModules = array_merge(
				$subscribingModules,
				$subscriber->getAffectedRLModules()
			);
		}

		return [
			'vars' => $vars,
			'modules' => $subscribingModules
		];
	}
}
