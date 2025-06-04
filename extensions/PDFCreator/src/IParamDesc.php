<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Message\Message;

interface IParamDesc {

	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @return Message
	 */
	public function getMessage(): Message;

	/**
	 * @return string
	 */
	public function getExampleValue(): string;
}
