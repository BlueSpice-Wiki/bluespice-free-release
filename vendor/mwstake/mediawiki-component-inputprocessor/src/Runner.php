<?php

namespace MWStake\MediaWiki\Component\InputProcessor;

use Exception;
use Psr\Log\LoggerInterface;
use StatusValue;
use Throwable;

class Runner {

	/** @var ProcessorFactory */
	private $processorFactory;

	/** @var LoggerInterface */
	private $logger;

	/**
	 * @param ProcessorFactory $processorFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct( ProcessorFactory $processorFactory, LoggerInterface $logger ) {
		$this->processorFactory = $processorFactory;
		$this->logger = $logger;
	}

	/**
	 * @param array $processors
	 * @param array $inputData
	 * @return StatusValue
	 * @throws Exception
	 */
	public function process( array $processors, array $inputData ): StatusValue {
		$output = [];

		$globalStatus = StatusValue::newGood();
		foreach ( $processors as $key => $processor ) {
			if ( is_array( $processor ) ) {
				try {
					$processor = $this->processorFactory->createWithData( $processor['type'] ?? '', $processor );
				} catch ( Throwable $ex ) {
					$globalStatus->setOK( false );
					$globalStatus->error( $ex->getMessage(), $key );
					continue;
				}

			}
			if ( !$processor instanceof IProcessor ) {
				$globalStatus->setOK( false );
				$globalStatus->error( 'inputprocessor-error-invalid-processor-object', $key );
				continue;
			}
			$value = $inputData[$key] ?? null;
			$status = $processor->process( $value, $key );
			if ( $status->isGood() ) {
				$output[$key] = $status->getValue();
			} else {
				$this->logger->error( "Error processing input $key: " . $status->getValue() );
				$globalStatus->setOK( false );
				$globalStatus->merge( $status );
			}
		}
		if ( !$globalStatus->isOK() ) {
			return $globalStatus;
		}
		return StatusValue::newGood( $output );
	}
}
