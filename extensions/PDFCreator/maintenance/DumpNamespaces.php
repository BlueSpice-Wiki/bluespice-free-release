<?php

/**
 * @copyright Copyright (C) 2025 Hallo Welt! GmbH
 * @author Daniel Vogel
 */

use MediaWiki\Extension\PDFCreator\ITargetResult;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportSpecification;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\IMaintainableDatabase;

$IP = dirname( __DIR__, 3 );

require_once "$IP/maintenance/Maintenance.php";

class DumpNamespaces extends Maintenance {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var NamespaceInfo */
	private $namespaceInfo;

	/** @var UserFactory */
	private $userFactory;

	/** @var PDFCreator */
	private $pdfCreator;

	/** @var UserIdentity|null */
	private $user = null;

	/** @var int|null */
	private $limit = null;

	/** @var bool */
	private $verbose = false;

	/** @var string */
	private $template = '';

	/** @var string */
	private $dest = '';

	/** @var mailRecipient|null */
	private $mailRecipient = null;

	/** @var string */
	private $mailSubject = '';

	/** @var array */
	private $mailData = [];

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->addOption( 'user', 'Username for export context.', true, true, 'u' );
		$this->addOption( 'dest', 'Absolute path of the output pdf file.', true, true, 'p' );
		$this->addOption( 'template', 'PDF template name', false, true, 't' );
		$this->addOption(
			'limit',
			'Limit the number of wiki pages for each pdf.',
			false, true, 'l'
		);
		$this->addOption( 'verbose', 'Verbose output', false, false, 'v' );
		$this->addOption( 'mail-recipient', 'E-mail recipient for notification email', false, true, 'm' );
		$this->addOption( 'mail-subject', 'E-mail subject for notification email', false, true, 'm' );
	}

	/**
	 * @return void
	 */
	public function execute() {
		$this->setupServices();

		$this->setUser();
		$this->setDest();
		$this->setTemplate();
		$this->setLimit();
		$this->setVerboseState();
		$this->setEmailRecipient();
		$this->setEmailSubject();

		if ( !$this->user ) {
			$this->output( "No valid user given.\n" );
		}

		if ( $this->verbose ) {
			$this->output( "Starting wiki dump...\n" );
		}

		$this->exportContentNamespaces();

		if ( $this->mailRecipient instanceof MailAddress ) {
			$this->sendMail();
		}

		if ( $this->verbose ) {
			$this->output( "Complete\n" );
		}
	}

	/**
	 * @return void
	 */
	private function setupServices(): void {
		$services = MediaWikiServices::getInstance();
		$this->titleFactory = $services->getTitleFactory();
		$this->namespaceInfo = $services->getNamespaceInfo();
		$this->userFactory = $services->getUserFactory();
		$this->pdfCreator = $services->get( 'PDFCreator' );
	}

	/**
	 * @return void
	 */
	private function setUser(): void {
		$user = $this->getOption( 'user', '' );
		$this->user = $this->userFactory->newFromName( $user );
	}

	/**
	 * @return void
	 */
	private function setDest(): void {
		$this->dest = $this->getOption( 'dest', '' );
	}

	/**
	 * @return void
	 */
	private function setTemplate(): void {
		$this->template = $this->getOption( 'template', '' );
	}

	/**
	 * @return void
	 */
	private function setLimit(): void {
		$limit = $this->getOption( 'limit', null );

		if ( $limit !== null ) {
			$this->limit = (int)$limit;
		} else {
			$this->limit = null;
		}
	}

	/**
	 * @return void
	 */
	private function setVerboseState(): void {
		$verbose = $this->getOption( 'verbose', false );

		if ( !$verbose ) {
			$this->verbose = false;
		} else {
			$this->verbose = true;
		}
	}

	/**
	 * @return void
	 */
	private function setEmailRecipient(): void {
		$mail = $this->getOption( 'mail-recipient', null );

		if ( !$mail ) {
			$this->mailRecipient = null;
		} elseif ( Sanitizer::validateEmail( $mail ) ) {
			$this->mailRecipient = new MailAddress( $mail );
		} else {
			$this->mailRecipient = null;
		}
	}

	/**
	 * @return void
	 */
	private function setEmailSubject(): void {
		$subject = $this->getOption( 'mail-subject', null );
		if ( !$subject ) {
			$this->mailSubject = null;
		} else {
			$this->mailSubject = $subject;
		}
	}

	/**
	 * @return void
	 */
	private function exportContentNamespaces(): void {
		/** @var IMaintainableDatabase */
		$dbr = $this->getDB( DB_REPLICA );

		$namespaces = $this->namespaceInfo->getContentNamespaces();

		foreach ( $namespaces as $namespace ) {
			$res = $dbr->select(
				'page',
				[ 'page_title', 'page_namespace' ],
				[ 'page_namespace' => $namespace ],
				__METHOD__,
				[]
			);

			$result = [];
			foreach ( $res as $row ) {
				$result[] = $row;
			}

			$pageSpecData = $this->makePageSpecData( $result );
			$specs = $this->prepareExportSpecifications( $pageSpecData );

			$this->doExport( $specs );
		}
	}

	/**
	 * @param array $result
	 * @return array
	 */
	private function makePageSpecData( array $result ): array {
		$data = [];
		$splitData = [];
		$namespaceName = '';
		$counter = 0;
		foreach ( $result as $page ) {
			$title = $this->titleFactory->makeTitle( $page->page_namespace, $page->page_title );
			if ( $namespaceName === '' ) {
				$namespaceName = $this->getNamespaceName( $title->getNsText() );
			}
			if ( is_int( $this->limit ) && count( $splitData ) >= $this->limit ) {
				$data[$namespaceName] = $splitData;
				$splitData = [];
				$counter++;
				$counterString = (string)$counter;
				$namespaceName = $this->getNamespaceName( $title->getNsText(), $counterString );
			}
			if ( !isset( $data[$namespaceName] ) ) {
				$data[$namespaceName] = [];
			}
			$splitData[] = [
				'type' => 'page',
				'target' => $title->getPrefixedDBkey()
			];
		}
		// Adding remaining pages afer last split
		if ( !empty( $splitData ) ) {
			$data[$namespaceName] = $splitData;
		}

		return $data;
	}

	/**
	 * @param string $nsText
	 * @param string $counter
	 * @return string
	 */
	private function getNamespaceName( string $nsText, string $counter = '' ): string {
		$namespaceName = ( $nsText === '' ) ? 'Main namespace' : $nsText;
		if ( $counter !== '' ) {
			$namespaceName .= " ($counter)";
		}
		return $namespaceName;
	}

	/**
	 * @param array $data
	 * @return ExportSpecification[]
	 */
	private function prepareExportSpecifications( array $data ): array {
		$specs = [];
		foreach ( $data as $namespaceName => $pageSpecs ) {
			$params = [
				"title" => str_replace( "_", " ", $namespaceName ),
				"filesystem-path" => $this->dest,
				"filename" => str_replace( " ", "_", "{$namespaceName}.pdf" )
			];
			if ( $this->template !== '' ) {
				$params["template"] = $this->template;
			}
			$options = [];
			$specs[] = new ExportSpecification(
				'batch', '', 'filesystem', '', $pageSpecs, $params, $options
			);
		}
		return $specs;
	}

	/**
	 * @param array $specs
	 * @return void
	 */
	private function doExport( array $specs ): void {
		$context = new ExportContext( $this->user );

		foreach ( $specs as $specification ) {
			$params = $specification->getParams();
			$filename = $params['filename'] ?? 'unknown.pdf';
			$result = $this->pdfCreator->create( $specification, $context );
			$exportResult = $result->getResult();
			if ( $exportResult instanceof ITargetResult === false ) {
				$this->output( "PDFCreator could not create pdf for filename: {$filename}\n" );
			}
			$exportStatus = $exportResult->getStatus();
			$success = false;
			if ( !$exportStatus->isGood() ) {
				$this->output( "PDFCreator failed creating pdf for filename: {$filename}\n" );
				$this->output( $exportStatus->getText() );
			} else {
				$sucess = true;
			}
			$this->addMailData( $filename, $sucess );
		}
	}

	/**
	 * @return MailAddress|null
	 */
	private function getMailSenderAddress(): ?MailAddress {
		$sender = $GLOBALS['wgPasswordSender'];

		if ( !Sanitizer::validateEmail( $sender ) ) {
			return null;
		}

		return new MailAddress( $sender );
	}

	/**
	 * @return void
	 */
	private function sendMail() {
		if ( $this->mailRecipient == null ) {
			$this->error(
				'Not a valid user name or e-mail address or user has no e-mail address set.'
			);
		} elseif ( $this->getMailSenderAddress() === null ) {
			$this->error(
				'wgPasswordSender not valid.'
			);
		} else {
			$status = UserMailer::send(
				$this->mailRecipient,
				$this->getMailSenderAddress(),
				$this->getMailSubject(),
				$this->getMailBody()
			);

			if ( $this->verbose === true ) {
				if ( $status->isGood() ) {
					$this->output( "Mail send\n" );
				} else {
					$this->output( "Mail error: " . $status->getMessage() );
				}
			}
		}
	}

	/**
	 * @param string $filename
	 * @param bool $success
	 * @return void
	 */
	private function addMailData( string $filename, bool $success ): void {
		$this->mailData[] = [ $filename, $success ];
	}

	/**
	 * @return string
	 */
	private function getMailSubject(): string {
		$sitename = $GLOBALS['wgSitename'];
		$subject = "{$this->mailSubject} ({$sitename})";

		return $subject;
	}

	/**
	 * @return string
	 */
	private function getMailBody(): string {
		$body = '';

		$sitename = $GLOBALS['wgSitename'];
		$timestampNow = wfTimestampNow();
		$timestamp = wfTimestamp( TS_RFC2822, $timestampNow );
		$timestampParts = explode( ',', $timestamp );
		array_shift( $timestampParts );
		$timestamp = implode( ',', $timestampParts );

		$text = wfMessage( "pdfcreator-dump-namespaces-mail-body-text", [
			$sitename,
			trim( $timestamp )
		] )->text();

		$body = "{$text}\n\n";

		foreach ( $this->mailData as $data ) {
			$body .= '- ' . $data[0];

			if ( $data[1] ) {
				$status = wfMessage( "pdfcreator-dump-namespaces-mail-body-text-done" )->text();
			} else {
				$status = wfMessage( "pdfcreator-dump-namespaces-mail-body-text-fail" )->text();
			}

			$body .= " {$status}\n";
		}

		return $body;
	}
}

$maintClass = DumpNamespaces::class;
require_once RUN_MAINTENANCE_IF_MAIN;
