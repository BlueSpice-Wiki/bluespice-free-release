<?php

use BlueSpice\ExtendedStatistics\AttributeRegistryFactory;
use BlueSpice\ExtendedStatistics\DiagramFactory;
use BlueSpice\ExtendedStatistics\IReport;
use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\ISnapshotStore;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;

return [

	'BSExtendedStatisticsDiagramFactory' => static function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceExtendedStatisticsDiagramRegistry'
		);
		return new DiagramFactory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'ExtendedStatisticsSnapshotProviderFactory' => static function ( MediaWikiServices $services ) {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceExtendedStatisticsSnapshotProviders'
		);
		return new AttributeRegistryFactory(
			$registry, $services->getObjectFactory(), ISnapshotProvider::class
		);
	},

	'ExtendedStatisticsReportFactory' => static function ( MediaWikiServices $services ) {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceExtendedStatisticsReports'
		);
		return new AttributeRegistryFactory(
			$registry, $services->getObjectFactory(), IReport::class
		);
	},

	'ExtendedStatisticsSnapshotStore' => static function ( MediaWikiServices $services ) {
		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceExtendedStatisticsSnapshotStores'
		);
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );
		$storeType = $config->get( 'StatisticsSnapshotStoreType' );

		if ( !isset( $registry[$storeType] ) ) {
			throw new MWException( 'Snapshot store ' . $storeType . ' is not registered' );
		}
		$specs = $registry[$storeType];
		if ( is_string( $specs ) && is_callable( $specs ) ) {
			$specs = [ 'factory' => $specs ];
		}
		// Forward-compatibility to MW1.34+
		$objectFactory = $services->getObjectFactory();

		$instance = $objectFactory->createObject( $specs );
		if ( !$instance instanceof ISnapshotStore ) {
			throw new MWException(
				"SnapshotStore must implement " . ISnapshotStore::class .
				', given ' . get_class( $instance )
			);
		}

		return $instance;
	},

	'ExtendedStatisticsSnapshotFactory' => static function ( MediaWikiServices $services ) {
		return new SnapshotFactory();
	}
];
