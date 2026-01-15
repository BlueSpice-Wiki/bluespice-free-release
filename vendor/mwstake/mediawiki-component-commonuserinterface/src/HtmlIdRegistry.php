<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Parser\Sanitizer;

class HtmlIdRegistry {

	/**
	 *
	 * @var HtmlIdRegistry|null
	 */
	private static $registry = null;

	/**
	 *
	 * @var array
	 */
	private $htmlIds = [];

	/**
	 *
	 * @var int
	 */
	private $limit = 1000;

	/**
	 *
	 * @return HtmlIdRegistry
	 */
	public static function singleton(): HtmlIdRegistry {
		if ( self::$registry === null ) {
			self::$registry = new HtmlIdRegistry();
		}

		return self::$registry;
	}

	/**
	 *
	 */
	public function __construct() {
		$this->htmlIds = [];
	}

	/**
	 *
	 * @param string $id
	 * @return string
	 */
	public function makeHtmlIdSafe( $id ): string {
		if ( !in_array( $id, $this->htmlIds ) ) {
			$newId = Sanitizer::escapeIdForAttribute( $id );
			array_push( $this->htmlIds, $newId );
			return $newId;
		}

		for ( $suffix = 1; $suffix <= $this->limit; $suffix++ ) {
			$newId = Sanitizer::escapeIdForAttribute( $id . '-' . $suffix );
			if ( !in_array( $newId, $this->htmlIds ) ) {
				$id = $newId;
				array_push( $this->htmlIds, $id );
				break;
			}
		}

		return $id;
	}
}
