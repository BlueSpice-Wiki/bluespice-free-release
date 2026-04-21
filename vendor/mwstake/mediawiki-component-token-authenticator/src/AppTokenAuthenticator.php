<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use Random\RandomException;

class AppTokenAuthenticator extends TokenAuthenticator {

	/**
	 * @return string
	 * @throws RandomException
	 */
	public function generateToken(): string {
		$data = [
			'app' => true,
		];
		return parent::doGenerateTokenWithIssuer( $data );
	}
}
