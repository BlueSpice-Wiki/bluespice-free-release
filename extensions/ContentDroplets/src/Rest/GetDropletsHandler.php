<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Rest;

use MediaWiki\Extension\ContentDroplets\DropletProvider;
use MediaWiki\Extension\ContentDroplets\DropletSerializer;
use MediaWiki\Extension\ContentDroplets\IDropletDescription;
use MediaWiki\Message\Message;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;

class GetDropletsHandler extends SimpleHandler {

	/**
	 * @var DropletProvider
	 */
	private DropletProvider $provider;

	/**
	 * @param DropletProvider $provider
	 */
	public function __construct( DropletProvider $provider ) {
		$this->provider = $provider;
	}

	/**
	 * @return Response
	 */
	public function run() {
		$droplets = $this->provider->getDroplets();
		$serializer = new DropletSerializer();
		return $this->getResponseFactory()->createJson( [
			'droplets' => $serializer->serialize( $droplets ),
			'categories' => $this->categorize( $droplets ),
		] );
	}

	/**
	 * @param IDropletDescription[] $droplets
	 * @return array
	 */
	private function categorize( array $droplets ): array {
		$categories = [
			'_all' => [
				'label' => Message::newFromKey( 'contentdroplets-category-label-all' )->text(),
				'droplets' => array_keys( $droplets ),
			]
		];
		foreach ( $droplets as $key => $droplet ) {
			$dropletCats = $droplet->getCategories();
			foreach ( $dropletCats as $dropletCat ) {
				if ( !isset( $categories[$dropletCat] ) ) {
					$categories[$dropletCat] = [
						'label' => Message::newFromKey(
							'contentdroplets-category-label-' . $dropletCat
						)->text(),
						'droplets' => [],
					];
				}
				$categories[$dropletCat]['droplets'][] = $key;
			}
		}

		return $this->sortCategories( $categories );
	}

	/**
	 * Make sure featured and all are first in the list
	 * @param array $categories
	 * @return array
	 */
	private function sortCategories( array $categories ): array {
		$sorted = [];
		if ( isset( $categories['featured'] ) ) {
			$sorted['featured'] = $categories['featured'];
			unset( $categories['featured'] );
		}

		ksort( $categories );
		return $sorted + $categories;
	}
}
