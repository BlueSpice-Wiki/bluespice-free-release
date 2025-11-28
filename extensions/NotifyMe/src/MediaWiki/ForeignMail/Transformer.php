<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ForeignMail;

use Exception;
use MailAddress;
use MediaWiki\Extension\NotifyMe\Channel\Email\MailContentProvider;
use MediaWiki\Extension\NotifyMe\MediaWiki\ForeignMail\Processor\IProcessor;
use MediaWiki\Hook\UserMailerTransformMessageHook;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;

/**
 * TODO: This class lost a lot of its meaning since we dont use the "wrapper" anymore
 */
class Transformer implements UserMailerTransformMessageHook {
	/**
	 * @var MailContentProvider
	 */
	private $mailContentProvider;
	/** @var IProcessor[] */
	private $processors;
	/** @var LoggerInterface */
	protected $logger;

	/**
	 * @param MailContentProvider $mailContentProvider
	 * @param LoggerInterface $logger
	 */
	public function __construct( MailContentProvider $mailContentProvider, LoggerInterface $logger ) {
		$this->mailContentProvider = $mailContentProvider;
		$this->logger = $logger;
		// Don't think we need a registry for this, yet
		$this->processors = [
			new Processor\Links(),
			new Processor\LineBreak()
		];
	}

	/**
	 * @param array $headers
	 *
	 * @return bool
	 */
	private function isPlainText( array $headers ) {
		return str_contains( $headers['Content-type'] ?? $headers['Content-Type'] ?? 'text/plain', 'text/plain' );
	}

	/**
	 * @param string $body
	 *
	 * @return string
	 */
	private function transformToHtml( $body ): string {
		foreach ( $this->processors as $processor ) {
			$body = $processor->process( $body );
		}
		return $body;
	}

	/**
	 * @inheritDoc
	 */
	public function onUserMailerTransformMessage( $to, $from, &$subject, &$headers, &$body, &$error ) {
		$this->logger->debug( 'Transformer::onUserMailerTransformMessage $body=' . $body );

		if ( $this->isPlainText( $headers ) ) {
			try {
				$this->quotePeriodUsernames( $to, $body );
				$body = $this->transformToHtml( $body );
				$this->unquotePeriodUsernames( $to, $body );

				$body = $this->mailContentProvider->wrap(
					$body,
					User::newSystemUser( 'MediaWiki default' )
				);

				$headers['Content-type'] = 'text/html; charset=UTF-8';
			} catch ( Exception $e ) {
				// Do nothing, we'll just send the plain text email
			}
		}
	}

	/**
	 * Wrap usernames containing a period in quotes
	 * Used to preserve usernames in LineBreak::fixWhitespaces
	 *
	 * @param MailAddress[] $to
	 * @param string &$body
	 * @return string
	 */
	private function quotePeriodUsernames( $to, string &$body ): void {
		foreach ( $to as $t ) {
			if ( str_contains( $t->name, '.' ) ) {
				$body = str_replace( $t->name, "\"$t->name\"", $body );
			}
		}
	}

	/**
	 * Restore quoted usernames containing a period back to original
	 *
	 * @param MailAddress[] $to
	 * @param string &$body
	 * @return string
	 */
	private function unquotePeriodUsernames( $to, string &$body ): void {
		foreach ( $to as $t ) {
			if ( str_contains( $t->name, '.' ) ) {
				$body = str_replace( "\"$t->name\"", $t->name, $body );
			}
		}
	}

}
