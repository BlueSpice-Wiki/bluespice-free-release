<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use InvalidArgumentException;
use MediaWiki\WikiMap\WikiMap;
use Random\RandomException;
use Wikimedia\ObjectCache\BagOStuff;

class TokenAuthenticator {
	protected const TTL = 10;

	/**
	 * @param BagOStuff $sessionCache
	 * @param string $salt
	 */
	public function __construct(
		private readonly BagOStuff $sessionCache,
		private readonly string $salt = ''
	) {
	}

	/**
	 * @param array|null $data
	 * @return string
	 * @throws RandomException
	 */
	public function doGenerateToken( ?array $data = [] ): string {
		$data = array_merge( [
			'wiki' => WikiMap::getCurrentWikiId(),
		], $data );

		$token = bin2hex( random_bytes( 16 ) );
		if ( $this->sessionCache->set( $this->sessionCache->makeKey( $token ), $data, self::TTL ) ) {
			return $token;
		} else {
			throw new InvalidArgumentException( 'Failed to store token in cache.' );
		}
	}

	/**
	 * Generates a token and bakes in the issuer, to be used for verification.
	 * Salt must be set for this method to work
	 *
	 * @param array $data
	 * @return string
	 * @throws RandomException
	 */
	public function doGenerateTokenWithIssuer( array $data = [] ) {
		if ( !$this->salt ) {
			throw new InvalidArgumentException( 'Salt must be set to generate a token with issuer.' );
		}
		$token = $this->doGenerateToken( $data );
		$callbackUrl = wfScript( 'rest' );
		$signature = hash_hmac( 'sha256', "$callbackUrl$token", $this->salt );
		return base64_encode( json_encode( [
			'verifyCallback' => $callbackUrl,
			'token' => $token,
			'sig' => $signature,
		] ) );
	}

	/**
	 * @param string $token
	 * @return array|null if invalid, otherwise token data
	 */
	public function doVerifyToken( string $token ): ?array {
		$key = $this->sessionCache->makeKey( $token );
		$value = $this->sessionCache->get( $key );
		if ( !$value ) {
			return null;
		}
		return $value;
	}
}
