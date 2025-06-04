<?php

namespace BlueSpice\Authors\Data\PageAuthors;

use BlueSpice\Renderer\Params;
use BlueSpice\RendererFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class SecondaryDataProvider extends \MWStake\MediaWiki\Component\DataStore\SecondaryDataProvider {

	/**
	 *
	 * @var RendererFactory
	 */
	protected $rendererfactory = null;

	/**
	 *
	 * @param RendererFactory $rendererfactory
	 */
	public function __construct( $rendererfactory ) {
		$this->rendererfactory = $rendererfactory;
	}

	/**
	 *
	 * @param Record &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$user = MediaWikiServices::getInstance()->getUserFactory()
			->newFromName( $dataSet->get( Record::USER_NAME ) );
		$dataSet->set( Record::USER_IMAGE_HTML, $this->makeImage( $user ) );
	}

	/**
	 *
	 * @param User $user
	 * @return string
	 */
	protected function makeImage( $user ) {
		$image = $this->rendererfactory->get( 'userimage', new Params( [
			'user' => $user,
			'width' => "48",
			'height' => "48"
		] ) );

		return $image->render();
	}
}
