<?php

namespace MediaWiki\Extension\NotifyMe;

use InvalidArgumentException;
use MediaWiki\HookContainer\HookContainer;

class EventProvider {

	/**
	 * @var array
	 */
	private $attribute;

	/**
	 * @var HookContainer
	 */
	private $hookContainer;

	/** @var bool */
	private $isInitialized = false;

	/** @var array */
	private $events = [];

	/**
	 * @param array $attribute
	 * @param HookContainer $hookContainer
	 */
	public function __construct( array $attribute, HookContainer $hookContainer ) {
		$this->attribute = $attribute;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @return array
	 */
	public function getRegisteredEvents(): array {
		$this->init();
		return $this->events;
	}

	/**
	 * Initialize event list
	 * @return void
	 */
	private function init() {
		if ( $this->isInitialized ) {
			return;
		}
		$events = $this->attribute;
		$this->hookContainer->run( 'NotifyMeRegisterEvents', [ &$events ] );

		$this->events = $events;
		foreach ( $events as $key => $definition ) {
			if ( !is_string( $key ) ) {
				throw new InvalidArgumentException( "Event key must be a string, $key provided" );
			}
			if ( !is_array( $definition ) ) {
				throw new InvalidArgumentException( "Event spec for \"$key\" must be an array" );
			}
			if ( !isset( $definition['buckets'] ) ) {
				throw new InvalidArgumentException( "Event spec for \"$key\" must have a 'buckets' key" );
			}
		}
		$this->isInitialized = true;
	}
}
