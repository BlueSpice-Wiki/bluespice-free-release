<?php

namespace MWStake\MediaWiki\Component\InputProcessor;

use Exception;
use InvalidArgumentException;
use MediaWiki\HookContainer\HookContainer;
use Wikimedia\ObjectFactory\ObjectFactory;

class ProcessorFactory {

	/** @var array */
	private array $registry;

	/** @var ObjectFactory */
	private ObjectFactory $objectFactory;

	/** @var HookContainer */
	private HookContainer $hookContainer;

	/** @var bool */
	private bool $initialized = false;

	/**
	 * @param array $registry
	 * @param ObjectFactory $objectFactory
	 * @param HookContainer $hookContainer
	 */
	public function __construct( array $registry, ObjectFactory $objectFactory, HookContainer $hookContainer ) {
		$this->registry = $registry;
		$this->objectFactory = $objectFactory;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @param string $name
	 * @param array $data
	 * @return IProcessor
	 * @throws Exception
	 */
	public function createWithData( string $name, array $data ): IProcessor {
		$processor = $this->create( $name );
		$processor->initializeFromSpec( $data );
		return $processor;
	}

	/**
	 * @param string $type
	 * @return IProcessor
	 * @throws Exception
	 */
	public function create( string $type ): IProcessor {
		$this->init();
		$spec = $this->registry[$type] ?? null;
		if ( !$spec ) {
			throw new InvalidArgumentException( "inputprocessor-error-processor-not-registered" );
		}
		$object = $this->objectFactory->createObject( $spec );
		if ( !$object instanceof IProcessor ) {
			throw new InvalidArgumentException( "inputprocessor-error-invalid-processor-object" );
		}
		return $object;
	}

	private function init() {
		if ( $this->initialized ) {
			return;
		}
		$this->hookContainer->run(
			'MWStakeInputProcessorRegisterProcessors',
			[ &$this->registry ]
		);
		$this->initialized = true;
	}
}
