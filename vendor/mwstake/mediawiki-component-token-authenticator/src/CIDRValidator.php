<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use Wikimedia\IPUtils;

class CIDRValidator {

	/**
	 * @param string|null $cidr
	 */
	public function __construct(
		private readonly ?string $cidr
	) {
		// If CIDR IS SET, validate it. If not set, it means there is no IP restriction, so we can skip validation.
		if ( $this->cidr && !IPUtils::isValidRange( $this->cidr ) ) {
			throw new \InvalidArgumentException( 'Invalid CIDR range provided' );
		}
	}

	public function validateIP( string $ip ): bool {
		if ( !$this->cidr ) {
			return true;
		}
		return IPUtils::isInRange( $ip, $this->cidr );
	}
}
