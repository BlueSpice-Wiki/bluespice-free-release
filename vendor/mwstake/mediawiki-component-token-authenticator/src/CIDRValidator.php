<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use InvalidArgumentException;
use Wikimedia\IPUtils;

class CIDRValidator {

	/**
	 * Fallback CIDR range, used if none is configured. Allows local access only.
	 */
	public const DEFAULT_CIDR = '127.0.0.1/32';

	/**
	 * Check if given value is a usable, non-empty CIDR range
	 *
	 * @param mixed $cidr
	 * @return bool
	 */
	public static function isValidCIDR( $cidr ): bool {
		return is_string( $cidr ) && $cidr !== '' && IPUtils::isValidRange( $cidr );
	}

	/**
	 * @param string $cidr
	 * @return void
	 * @throws InvalidArgumentException If CIDR is empty or malformed
	 */
	public function assertValidCIDR( string $cidr ): void {
		if ( $cidr === '' ) {
			throw new InvalidArgumentException(
				'CIDR range must not be empty - CIDR restriction cannot be disabled'
			);
		}
		if ( !IPUtils::isValidRange( $cidr ) ) {
			throw new InvalidArgumentException( 'Invalid CIDR range provided' );
		}
	}

	/**
	 * @param string $ip
	 * @param string $cidr Must be a valid, non-empty CIDR range
	 * @return bool
	 * @throws InvalidArgumentException If CIDR is empty or malformed
	 */
	public function validateIP( string $ip, string $cidr ): bool {
		$this->assertValidCIDR( $cidr );
		return IPUtils::isInRange( $ip, $cidr );
	}
}
