<?php

namespace BlueSpice\InstanceStatus;

use InvalidArgumentException;
use Wikimedia\ObjectFactory\ObjectFactory;

class StatusReport {

	/** @var IStatusProvider[]|null */
	private ?array $uiStatusProviders = null;

	/** @var IApiStatusProvider[]|null */
	private ?array $apiStatusProviders = null;

	/**
	 * @param ObjectFactory $objectFactory
	 * @param array $uiStatusProviderSpecs
	 * @param array $apiStatusProviderSpecs
	 */
	public function __construct(
		private readonly ObjectFactory $objectFactory,
		private readonly array $uiStatusProviderSpecs,
		private readonly array $apiStatusProviderSpecs
	) {
	}

	/**
	 * @return array
	 */
	public function getReportForUI(): array {
		$this->uiStatusProviders =
			$this->uiStatusProviders ??
			$this->makeProviders( $this->uiStatusProviderSpecs, IStatusProvider::class );

		$statusInfo = [];
		foreach ( $this->uiStatusProviders as $provider ) {
			$statusInfo[] = [
				'label' => $provider->getLabel(),
				'value' => $provider->getValue(),
				'icon' => $provider->getIcon(),
				'priority' => $provider->getPriority()
			];
		}

		usort( $statusInfo, static function ( $a, $b ) {
			if ( $a['priority'] === $b['priority'] ) {
				return 0;
			}
			return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
		} );

		return $statusInfo;
	}

	/**
	 * @return array
	 */
	public function getReportForAPI(): array {
		$this->apiStatusProviders =
			$this->uiStatusProviders ??
			$this->makeProviders( $this->apiStatusProviderSpecs, IApiStatusProvider::class );

		$statusInfo = [];
		foreach ( $this->apiStatusProviders as $provider ) {
			$statusInfo[$provider->getKeyForApi()] = $provider->getValueForApi();
		}

		return $statusInfo;
	}

	/**
	 * @param array $specs
	 * @param string $mustImplement
	 * @return IStatusProvider[]|IApiStatusProvider[]
	 */
	private function makeProviders( array $specs, string $mustImplement ): array {
		$providers = [];
		foreach ( $specs as $spec ) {
			$provider = $this->objectFactory->createObject( $spec );
			if ( !is_a( $provider, $mustImplement ) ) {
				throw new InvalidArgumentException(
					"Instance status provider must implement $mustImplement"
				);
			}
			$providers[] = $provider;
		}
		return $providers;
	}
}
