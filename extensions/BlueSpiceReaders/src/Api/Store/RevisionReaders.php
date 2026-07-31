<?php
namespace BlueSpice\Readers\Api\Store;

use BlueSpice\Context;
use BlueSpice\Readers\Data\RevisionReaders\Store;

class RevisionReaders extends \BlueSpice\Api\Store {

	/**
	 * @inheritDoc
	 */
	protected function makeDataStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			$this->services->getDBLoadBalancer()
		);
	}
}
