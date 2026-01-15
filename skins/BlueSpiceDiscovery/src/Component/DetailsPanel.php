<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;

class DetailsPanel extends SimpleCard implements IRestrictedComponent {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'details-pnl';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		return [ 'w-100', 'bg-transp' ];
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLModules(): array {
		return [ 'skin.discovery.details-panel.scripts' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		$actions = [];
		if ( !empty( $this->componentProcessData['panel']['details'] ) ) {
			$actions = $this->componentProcessData['panel']['details'];
		}
		if ( !empty( $this->componentProcessData['actioncollection'] ) ) {
			$toolbox = [];
			foreach ( $this->componentProcessData['actioncollection'] as $name => $collection ) {
				if ( empty( $collection ) ) {
					continue;
				}
				$toolbox[$name] = $linkFormatter->formatLinks( $collection );
			}
			$this->arrangePanels( $toolbox );
			if ( !empty( $toolbox ) ) {
				$actions['details'] = [
					'text' => Message::newFromKey( 'bs-discovery-details-pnl-more-link-text' ),
					'title' => Message::newFromKey( 'bs-discovery-details-pnl-more-link-title' ),
					'aria' => [ 'label' => Message::newFromKey( 'bs-discovery-details-pnl-more-link-aria-label' ) ],
					'href' => '',
					'class' => false,
					'data' => [ 'toolbox' => FormatJson::encode( $toolbox ) ],
					'id' => 'ca-details'
				];
			}
		}

		if ( empty( $actions ) ) {
			return [];
		}

		return [
			new SimpleCardHeader( [
				'id' => "{$this->getId()}-head",
				'classes' => [ 'menu-title' ],
				'items' => [
					new Literal(
						"{$this->getId()}-title",
						Message::newFromKey( 'bs-discovery-sidebar-details-heading' )->text()
					)
				]
			] ),
			new SimpleLinklistGroupFromArray( [
				'id' => "{$this->getId()}-list",
				'classes' => [],
				'aria' => [
					'labelledby' => "{$this->getId()}-head"
				],
				'links' => $linkFormatter->formatLinks( $this->sortLinks( $actions ) ),
				'role' => 'group',
				'item-role' => 'presentation'
			] ),
		];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 * @return array
	 */
	protected function getFavoritePositions(): array {
		return [
			'ca-history' => 10,
			't-info' => 20,
			// TODO: move handling of t-smwbrowselink to BlueSpiceSMWConnector
			// whenever we deploy a version, that does not use deprecated toolbox
			// hook anymore
			't-smwbrowselink' => 30,
			'details' => 100,
		];
	}

	/**
	 * @param array $links
	 * @return array
	 */
	protected function sortLinks( $links ): array {
		foreach ( $links as $key => &$data ) {
			if ( isset( $data['position'] ) ) {
				continue;
			}
			$data['position'] = isset( $this->getFavoritePositions()[$key] )
				? $this->getFavoritePositions()[$key]
				: 0;
		}
		usort( $links, static function ( $e1, $e2 ) {
			if ( $e1['position'] == $e2['position'] ) {
				return 0;
			}
			return $e1['position'] > $e2['position'] ? 1 : 0;
		} );
		return $links;
	}

	/**
	 *
	 * @param array &$panels
	 * @return void
	 */
	private function arrangePanels( &$panels ): void {
		$arrangedPanels = [];
		$this->moveFromPanel( $arrangedPanels, $panels, 'views' );
		$this->moveFromPanel( $arrangedPanels, $panels, 'namespaces' );
		$this->moveFromPanel( $arrangedPanels, $panels, 'actions' );
		$this->moveFromPanel( $arrangedPanels, $panels, 'toolbox' );
		$panels = array_merge( $arrangedPanels, $panels );
	}

	/**
	 *
	 * @param array &$targetPanel
	 * @param array &$sourcePanel
	 * @param string $key
	 * @return void
	 */
	private function moveFromPanel( &$targetPanel, &$sourcePanel, $key ) {
		if ( array_key_exists( $key, $sourcePanel ) ) {
			if ( array_key_exists( $key, $sourcePanel ) ) {
				$targetPanel = array_merge(
					$targetPanel,
					[
						$key => $sourcePanel[$key]
					]
				);
				unset( $sourcePanel[$key] );
			}
		}
	}
}
