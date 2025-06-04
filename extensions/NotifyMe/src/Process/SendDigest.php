<?php

namespace MediaWiki\Extension\NotifyMe\Process;

use Exception;
use MediaWiki\Extension\NotifyMe\Channel\Email\DigestCreator;
use MediaWiki\Extension\NotifyMe\Channel\EmailChannel;
use MediaWiki\Extension\NotifyMe\ChannelFactory;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Psr\Log\LoggerInterface;

abstract class SendDigest implements IProcessStep {

	/**
	 * @var NotificationStore
	 */
	private $store;
	/**
	 * @var ChannelFactory
	 */
	private $channelFactory;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param NotificationStore $store
	 * @param ChannelFactory $channelFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		NotificationStore $store, ChannelFactory $channelFactory, LoggerInterface $logger
	) {
		$this->store = $store;
		$this->channelFactory = $channelFactory;
		$this->logger = $logger;
	}

	/**
	 * @param array $data
	 * @return array|string[]
	 */
	public function execute( $data = [] ): array {
		$emailChannel = $this->channelFactory->getChannel( 'email' );
		if ( !( $emailChannel instanceof EmailChannel ) ) {
			return [ 'status' => 'skipped' ];
		}
		$rangeCondition = $this->getDateRangeCondition();
		$notifications = $this->store
			->forChannel( $emailChannel )
			->pending()
			->query( $rangeCondition );
		$perUser = [];
		foreach ( $notifications as $notification ) {
			$targetUser = $notification->getTargetUser();
			if ( $emailChannel->getFrequencyPreference( $targetUser ) !== $this->getTargetDigestPeriod() ) {
				continue;
			}
			if ( !isset( $perUser[$targetUser->getId()] ) ) {
				$perUser[$targetUser->getId()] = [
					'user' => $targetUser,
					'notifications' => [],
				];
			}
			$perUser[$targetUser->getId()]['notifications'][] = $notification;
		}

		$success = $fail = 0;
		foreach ( $perUser as $item ) {
			try {
				$emailChannel->digest( $item['user'], $item['notifications'], $this->getTargetDigestPeriod() );
				$this->logger->info( '{period} digest sent to user {user}', [
					'period' => ucfirst( $this->getTargetDigestPeriod() ),
					'user' => $item['user']->getName(),
				] );
				$success++;
			} catch ( Exception $ex ) {
				$this->logger->error( 'Cannot send digest (period: {period}) to user {user}: {error}', [
					'period' => $this->getTargetDigestPeriod(),
					'user' => $item['user']->getName(),
					'error' => $ex->getMessage(),
				] );
				$fail++;
			}
		}
		return [ 'status' => 'done', 'success' => $success, 'fail' => $fail, 'range' => $rangeCondition ];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getDateRangeCondition() {
		$now = new \DateTime();
		$end = clone $now;
		$start = clone $now;
		switch ( $this->getTargetDigestPeriod() ) {
			case DigestCreator::DIGEST_TYPE_DAILY:
				$start->modify( '-1 day' );
				break;
			case DigestCreator::DIGEST_TYPE_WEEKLY:
				$start->modify( '-1 week' );
				break;
		}
		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );

		return [
			'ne_timestamp > ' . $db->addQuotes( $start->format( 'YmdHis' ) ) .
			' AND ne_timestamp < ' . $db->addQuotes( $end->format( 'YmdHis' ) )
		];
	}

	/**
	 * DigestCreator::DIGEST_TYPE_DAILY or DigestCreator::DIGEST_TYPE_WEEKLY
	 * @return string
	 */
	abstract protected function getTargetDigestPeriod(): string;
}
