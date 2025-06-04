<?php

namespace MediaWiki\Extension\NotifyMe\Process;

use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use MWStake\MediaWiki\Component\ProcessManager\ProcessManager;
use Psr\Log\LoggerInterface;

class UpdateEventProcessStatus implements IProcessStep {
	/** @var NotificationStore */
	private $notificationStore;
	/** @var ProcessManager */
	private $processManager;
	/** @var LoggerInterface */
	private $logger;

	/**
	 * @param NotificationStore $notificationStore
	 * @param ProcessManager $processManager
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		NotificationStore $notificationStore, ProcessManager $processManager, LoggerInterface $logger
	) {
		$this->notificationStore = $notificationStore;
		$this->processManager = $processManager;
		$this->logger = $logger;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function execute( $data = [] ): array {
		$pending = $this->notificationStore->getPendingEventProcesses();

		foreach ( $pending as $eventProcess ) {
			if ( $this->processManager->getProcessStatus( $eventProcess['process'] ) === 'terminated' ) {
				$info = $this->processManager->getProcessInfo( $eventProcess['process'] );
				$exitMessage = $info->getExitCode();
				if ( $exitMessage === 0 ) {
					$this->notificationStore->updateEventProcessStatus( $eventProcess['id'], 'completed' );
				} else {
					$this->notificationStore->updateEventProcessStatus( $eventProcess['id'], 'failed' );
					$output = $info->getOutput()['error'] ?? '-';
					$this->logger->error(
						"Event process {$eventProcess['id']} failed with exit code {$exitMessage}: {$output}"
					);
				}
			}
		}

		return [];
	}
}
