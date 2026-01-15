<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\ForeignMail;

use MediaWiki\Extension\NotifyMe\Channel\Email\MailContentProvider;
use MediaWiki\Hook\UserMailerTransformMessageHook;
use MediaWiki\User\User;

/**
 * TODO: This class lost a lot of its meaning since we dont use the "wrapper" anymore
 */
class Transformer implements UserMailerTransformMessageHook {
	/**
	 * @var MailContentProvider
	 */
	private $mailContentProvider;
	/** @var array */
	private $processors;

	/**
	 * @param MailContentProvider $mailContentProvider
	 */
	public function __construct( MailContentProvider $mailContentProvider ) {
		$this->mailContentProvider = $mailContentProvider;
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
		if ( $this->isPlainText( $headers ) ) {
			try {
				$body = $this->mailContentProvider->wrap(
					$this->transformToHtml( $body ),
					User::newSystemUser( 'MediaWiki default' )
				);

				$headers['Content-type'] = 'text/html; charset=UTF-8';
			} catch ( \Exception $e ) {
				// Do nothing, we'll just send the plain text email
			}
		}
	}
}
