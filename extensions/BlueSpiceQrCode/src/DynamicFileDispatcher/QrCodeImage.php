<?php

namespace BlueSpice\QrCode\DynamicFileDispatcher;

use BsFileSystemHelper;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use File;
use MediaWiki\Message\Message;
use MediaWiki\Rest\Stream;
use MediaWiki\Status\Status;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use Psr\Http\Message\StreamInterface;

class QrCodeImage implements IDynamicFile {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var string */
	private $pagename;

	/** @var string */
	private $query;

	/** @var int */
	private $size;

	/**
	 * @param TitleFactory $titleFactory
	 * @param string $pagename
	 * @param string $query
	 * @param int $size
	 */
	public function __construct( TitleFactory $titleFactory, string $pagename, string $query, int $size ) {
		$this->titleFactory = $titleFactory;
		$this->pagename = $pagename;
		$this->query = $query;
		$this->size = $size;
	}

	/**
	 * @inheritDoc
	 */
	public function getMimeType(): string {
		return 'image/png';
	}

	/**
	 * @inheritDoc
	 */
	public function getStream(): StreamInterface {
		[ $status, $file ] = $this->generate();
		if ( !$status->isGood() || !$file ) {
			$messages = $status->getMessages( null );
			$errorMessage = [];
			foreach ( $messages as $message ) {
				$errorMessage[] = Message::newFromSpecifier( $message )->text();
			}
			throw new Exception(
				'FATAL: QrCode could not be saved! ' . implode( ', ', $errorMessage )
			);
		}
		return new Stream( fopen( $file->getLocalRefPath(), 'rb' ) );
	}

	/**
	 * @return array
	 */
	public function generate(): array {
		$id = md5( $this->pagename . $this->query . $this->size );
		$filename = ucfirst( $id . ".png" );
		$file = BsFileSystemHelper::getFileFromRepoName(
			$filename,
			'QrCode'
		);

		if ( $file instanceof File && $file->exists() ) {
			return [ Status::newGood(), $file ];
		}
		$title = $this->titleFactory->newFromText( $this->pagename );
		$url = $title->getFullURL( $this->query );

		$qrCode = new QrCode( $url );
		$qrCode->setSize( $this->size );
		$writer = new PngWriter();
		$result = $writer->write( $qrCode );
		$qrCodeSrc = $result->getString();

		$status = BsFileSystemHelper::saveToDataDirectory(
			$filename,
			$qrCodeSrc,
			'QrCode'
		);
		$file = BsFileSystemHelper::getFileFromRepoName(
			$filename,
			'QrCode'
		);
		return [ $status, $file ];
	}
}
