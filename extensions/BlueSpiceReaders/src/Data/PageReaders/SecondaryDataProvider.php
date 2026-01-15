<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Readers\Data\Record;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class SecondaryDataProvider extends \MWStake\MediaWiki\Component\DataStore\SecondaryDataProvider {

	/**
	 *
	 * @param Record &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$services = MediaWikiServices::getInstance();
		$factory = $services->getService( 'BSRendererFactory' );
		$user = $services->getUserFactory()->newFromId( $dataSet->get( Record::USER_ID ) );
		if ( $user instanceof User == false ) {
			return;
		}

		$image = $factory->get( 'userimage', new \BlueSpice\Renderer\Params( [
			'user' => $user,
			'width' => "48",
			'height' => "48",
		] ) );
		$dataSet->set( Record::USER_IMAGE_HTML, $image->render() );
		$userRealName = !empty( $user->getRealName() ) ? $user->getRealName() : $user->getName();
		$dataSet->set( Record::USER_REAL_NAME, $userRealName );
	}
}
