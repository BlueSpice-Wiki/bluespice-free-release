<?php

/**
 * @copyright Copyright (C) 2025 Hallo Welt! GmbH
 * @author Daniel Vogel
 */

use MediaWiki\Extension\PDFCreator\ITargetResult;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

$IP = dirname( __DIR__, 3 );

require_once "$IP/maintenance/Maintenance.php";

/**
 * Perform a pdf export using specification file.
 * Example see ../doc/specification.example.json
 *
 * @ingroup Maintenance
 */
class CreatePDF extends Maintenance {

	/**
	 */
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Exports a batch of pages.' );
		$this->addOption(
			'src',
			'Json specification file.',
			false,
			true
		);
	}

	/**
	 * @return bool|null|void
	 */
	public function execute() {
		$services = MediaWikiServices::getInstance();

		$pdfCreator = $services->get( 'PDFCreator' );
		if ( !$pdfCreator ) {
			echo "Service PDFCreator not found\n";
			return;
		}

		# Change to current working directory
		$oldCwd = getcwd();
		chdir( $oldCwd );

		if ( $this->getOption( 'src', false ) !== false ) {
			$src = $this->getOption( 'src' );
		} elseif ( $this->hasArg( 0 ) ) {
			$src = $this->getArg( 0 );
		} else {
			echo "Option --src not set\n";
			return;
		}

		$this->output( "Source:\t\t$src\n" );

		$json = file_get_contents( $src );
		$exportSpecificationFactory = $services->get( 'PDFCreator.ExportSpecificationFactory' );
		$specification = $exportSpecificationFactory->newFromJson( $json );

		$userFactory = $services->getUserFactory();

		$params = $specification->getParams();
		if ( isset( $params['user'] ) ) {
			$user = $userFactory->newFromName( $params['user'] );
		} else {
			echo "Missing user\n";
			return;
		}

		$relevantTitle = null;
		if ( isset( $params['relevantTitle'] ) ) {
			$titleFactory = $services->getTitleFactory();
			$relevantTitle = $titleFactory->newFromText( $params['relevantTitle'] );
		}

		$context = new ExportContext( $user, $relevantTitle	);

		$result = $pdfCreator->create( $specification, $context );
		$exportResult = $result->getResult();
		if ( $exportResult instanceof ITargetResult === false ) {
			echo 'PDFCreator return value does not contain not ITargetResult';
		}
		$exportStatus = $exportResult->getStatus();
		if ( !$exportStatus ) {
			echo $exportStatus->getText();
		} else {
			$data = $exportResult->getData();
			echo "done ... ";
			echo var_export( $data['data'], true ) . "\n";
		}
	}
}

$maintClass = CreatePDF::class;
require_once RUN_MAINTENANCE_IF_MAIN;
