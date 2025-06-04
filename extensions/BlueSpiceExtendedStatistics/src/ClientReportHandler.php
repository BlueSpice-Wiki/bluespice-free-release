<?php

namespace BlueSpice\ExtendedStatistics;

class ClientReportHandler {
	/** @var array|string */
	private $rlModules;
	/** @var string */
	private $class;

	/**
	 * @param array|string $modules
	 * @param string $class
	 */
	public function __construct( $modules, $class ) {
		if ( is_string( $modules ) ) {
			$modules = [ $modules ];
		}
		$this->rlModules = $modules;
		$this->class = $class;
	}

	/**
	 * Get required RL modules
	 *
	 * @return array
	 */
	public function getRLModules(): array {
		return $this->rlModules;
	}

	/**
	 *
	 *
	 * @return string JS class name
	 */
	public function getClass() {
		return $this->class;
	}
}
