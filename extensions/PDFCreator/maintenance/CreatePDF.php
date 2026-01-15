<?php

/**
 * @copyright Copyright (C) 2025 Hallo Welt! GmbH
 * @author Daniel Vogel
 */

use MediaWiki\Extension\PDFCreator\IContextSourceAware;
use MediaWiki\Extension\PDFCreator\IExportMode;
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

		$pages = $specification->getPageSpecs();

		$relevantTitle = null;
		$titleFactory = $services->getTitleFactory();
		if ( isset( $params['relevantTitle'] ) ) {
			$relevantTitle = $titleFactory->newFromText( $params['relevantTitle'] );
		} elseif ( !empty( $pages ) ) {
			foreach ( $pages as $page ) {
				if ( !isset( $page['target'] ) ) {
					continue;
				}
				$relevantTitle = $titleFactory->newFromText( $page['target'] );
				break;
			}
		}

		$context = new ExportContext( $user, $relevantTitle );

		// Use export Modes
		if ( isset( $params['mode'] ) && $relevantTitle !== null ) {
			$modeFactory = $services->get( 'PDFCreator.ExportModeFactory' );
			$modeProvider = $modeFactory->getModeProvider( $params['mode'] );
			if ( $modeProvider instanceof IExportMode ) {
				if ( $modeProvider instanceof IContextSourceAware ) {
					$requestContext = RequestContext::getMain();
					$requestContext->setUser( User::newFromIdentity( $context->getUserIdentity() ) );
					$requestContext->setTitle( Title::newFromPageIdentity( $context->getPageIdentity() ) );
					$modeProvider->setContext( $requestContext );
				}
				$pages = $modeProvider->getExportPages( $relevantTitle, $params );

				// Override ExportSpecificaton
				$specification = $exportSpecificationFactory->new(
					'batch',
					$specification->getTemplateProvider(),
					$specification->getTarget(),
					$specification->getBackend(),
					$pages,
					$specification->getOptions(),
					$specification->getParams()
				);
			} else {
				echo "Invalid mode provider";
			}
		}

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
