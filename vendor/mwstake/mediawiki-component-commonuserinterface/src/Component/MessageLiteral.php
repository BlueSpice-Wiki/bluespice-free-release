<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IMessageLiteral;

class MessageLiteral extends ComponentBase implements IMessageLiteral {

	/**
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 *
	 * @var Message
	 */
	private $msg = null;

	/**
	 *
	 * @param string $id
	 * @param Message $msg
	 */
	public function __construct( $id, $msg ) {
		$this->id = $id;
		$this->msg = $msg;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return $this->msg;
	}
}
