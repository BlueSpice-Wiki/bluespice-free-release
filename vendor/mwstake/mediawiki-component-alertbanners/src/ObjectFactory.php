<?php

namespace MWStake\MediaWiki\Component\AlertBanners;

use MediaWiki\MediaWikiServices;
use Wikimedia\ObjectFactory\ObjectFactory as WikimediaObjectFactory;

class ObjectFactory {

	/**
	 * See `Wikimedia/ObjectFactory::getObjectFromSpec` for details
	 *
	 * @param array $spec
	 * @return stdClass
	 * @throws InvalidArgumentException
	 * @throws UnexpectedValueException
	 */
	public static function getObjectFromSpec( $spec ) {
		$services = MediaWikiServices::getInstance();
		$config = $services->getMainConfig();
		$mwVersion = $config->get( 'Version' );
		if ( version_compare( $mwVersion, '1.35', '>=' ) ) {
			return $services->getObjectFactory()->createObject( $spec );
		} else {
			return WikimediaObjectFactory::getObjectFromSpec( $spec );
		}
	}
}
