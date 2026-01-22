<?php

namespace MWStake\MediaWiki\Component\Wire;

use JsonSerializable;
use MediaWiki\Message\Message;

class WireMessage implements JsonSerializable {

	/**
	 * Convenience method to create a new WireMessage from a MediaWiki Message object
	 *
	 * @param Message $message
	 * @param WireChannel $channel
	 * @return self
	 */
	public static function newFromLocalizationMessage( Message $message, WireChannel $channel ): self {
		// Otherwise, we return a new WireMessage with the localized text
		return new self( $channel, [
			'message' => [
				'key' => $message->getKey(), 'params' => $message->getParams()
			]
		] );
	}

	/**
	 * @param WireChannel $channel
	 * @param array|null $payload
	 */
	public function __construct(
		private readonly WireChannel $channel,
		private readonly ?array $payload
	) {
	}

	/**
	 * @return array|null
	 */
	public function getPayload(): ?array {
		return $this->payload;
	}

	/**
	 * @return WireChannel
	 */
	public function getChannel(): WireChannel {
		return $this->channel;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'channel' => (string)$this->channel,
			'payload' => $this->payload,
		];
	}
}
