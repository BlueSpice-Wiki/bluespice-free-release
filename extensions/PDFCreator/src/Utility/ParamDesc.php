<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Extension\PDFCreator\IParamDesc;
use MediaWiki\Message\Message;

class ParamDesc implements IParamDesc {

	/** @var string */
	private $key;

	/** @var Message */
	private $message;

	/** @var string */
	private $exampleValue;

	/**
	 * @param string $key
	 * @param Message $message
	 * @param string $exampleValue
	 */
	public function __construct( string $key, Message $message, string $exampleValue = '' ) {
		$this->key = $key;
		$this->message = $message;
		$this->exampleValue = $exampleValue;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return $this->key;
	}

	/**
	 * @return Message
	 */
	public function getMessage(): Message {
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getExampleValue(): string {
		return $this->exampleValue;
	}
}
