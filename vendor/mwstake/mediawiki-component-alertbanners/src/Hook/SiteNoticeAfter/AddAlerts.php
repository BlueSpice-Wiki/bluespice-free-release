<?php

namespace MWStake\MediaWiki\Component\AlertBanners\Hook\SiteNoticeAfter;

use GlobalVarConfig;
use Html;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\AlertBanners\IAlertProvider;
use MWStake\MediaWiki\Component\AlertBanners\IAlertProviderFactory;
use Skin;
use Wikimedia\ObjectFactory\ObjectFactory;

class AddAlerts {

	/**
	 *
	 * @param string &$siteNotice
	 * @param Skin $skin
	 * @return bool
	 */
	public static function callback( &$siteNotice, $skin ) {
		$objectFactory = MediaWikiServices::getInstance()->getObjectFactory();
		$provider = new static( $siteNotice, $skin, $objectFactory );
		return $provider->process();
	}

	/**
	 *
	 * @var string
	 */
	private $siteNotice = '';

	/**
	 *
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 *
	 * @param string &$siteNotice
	 * @param Skin $skin
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( &$siteNotice, $skin, $objectFactory ) {
		$this->siteNotice =& $siteNotice;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function process() {
		return $this->doProcess();
	}

	/** @var string */
	protected $container = '';

	/** @var array */
	protected $alerts = [];

	protected function doProcess() {
		$this->collectAlerts();
		$this->buildContainer();
		$this->addContainer();

		return true;
	}

	protected function collectAlerts() {
		$mwsAlertBanners = new GlobalVarConfig( 'mwsgAlertBanners' );
		$providerFactories = $mwsAlertBanners->get( 'ProviderFactories' );

		/** @var IAlertProvider[] */
		$providers = [];
		foreach ( $providerFactories as $registryKey => $providerFactorySpec ) {
			/** @var IAlertProviderFactory */
			$providerFactory = $this->objectFactory->createObject( $providerFactorySpec );
			$providers = $providerFactory->processProviders( $providers );
		}
		foreach ( $providers as $regKey => $provider ) {
			$this->alerts[$regKey] = $this->makeAlertHTML( $regKey, $provider );
		}
	}

	protected function buildContainer() {
		$rawContentHtml = '';

		foreach ( $this->alerts as $regKey => $html ) {
			$rawContentHtml .= $html;
		}

		$this->container = Html::rawElement(
			'div',
			[
				'id' => 'mwstake-alert-container'
			],
			$rawContentHtml
		);
	}

	/**
	 * HINT: This is basically what Extension:CentralNotice does, therefore I
	 * consider it to be a good approach
	 * https://github.com/wikimedia/mediawiki-extensions-CentralNotice/blob/949a5edc36a7ecb86642fc608633dd7336be36a4/CentralNotice.hooks.php#L277-L281
	 */
	protected function addContainer() {
		$this->siteNotice .= $this->container;
	}

	/**
	 *
	 * @param string $registryKey
	 * @param IAlertProvider $provider
	 * @return string
	 */
	protected function makeAlertHTML( $registryKey, $provider ) {
		$type = $provider->getType();
		$html = $provider->getHTML();

		if ( empty( $html ) ) {
			return '';
		}

		return Html::rawElement(
			'div',
			[
				'class' => 'alert alert-' . $type,
				'role' => "alert",
				'data-mwstake-alert-id' => $registryKey
			],
			$html
		);
	}

}
