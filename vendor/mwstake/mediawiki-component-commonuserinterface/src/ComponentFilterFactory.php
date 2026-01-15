<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use Wikimedia\ObjectFactory\ObjectFactory;

class ComponentFilterFactory {

	/** @var array */
	private $registeredFilters = [];

	/** @var ObjectFactory */
	private $objectFactory;

	/**
	 *
	 * @param array $filters
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( array $filters, ObjectFactory $objectFactory ) {
		$this->registeredFilters = $filters;
		$this->objectFactory = $objectFactory;
	}

	/**
	 *
	 * @return IComponentFilter[]
	 */
	public function getAllFilters() {
		$filters = [];
		foreach ( $this->registeredFilters as $key => $spec ) {
			$filters[$key] = $this->objectFactory->createObject( $spec );
		}

		return $filters;
	}

}
