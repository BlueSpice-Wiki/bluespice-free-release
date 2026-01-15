<?php

namespace BlueSpice\QrCode\Api;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use MediaWiki\Api\ApiBase;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

class QrCodeApi extends ApiBase {

	/**
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'page' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'revid' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => 0
			],
			'size' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => 120
			],
		];
	}

	/**
	 *
	 * @return void
	 */
	public function execute() {
		$status = Status::newFatal( 'bs-qr-code-api-no-page' );

		$page = trim(
			$this->getParameter( 'page' ),
			"'\"\x20"
		);

		if ( !empty( $page ) ) {
			$title = Title::newFromText( $page );
			if ( $title->exists() ) {
				$lastRevId = $title->getLatestRevID();
				$revId = $this->getParameter( 'revid' );
				$queryParams = [];
				if ( ( $revId > 0 ) && ( $revId <= $lastRevId ) ) {
					$queryParams['oldid'] = $revId;
				}
				$data = $this->getQrCodeData(
					$title->getFullURL( $queryParams ),
					$this->getParameter( 'size' )
				);
				$status = Status::newGood( $data );
			}
		}

		$result = $this->getResult();
		if ( $status->isOk() ) {
			$result->addValue( null, 'success', 1 );
			$result->addValue( null, 'data', $status->getValue() );
		} else {
			$result->addValue( null, 'success', 0 );
			$result->addValue( null, 'error', $status->getMessage() );
		}
	}

	/**
	 *
	 * @param string $url
	 * @param int $size
	 * @return string
	 */
	private function getQrCodeData( $url, $size ) {
		$qrCode = new QrCode( $url );
		$qrCode->setSize( $size );
		$writer = new PngWriter();
		$result = $writer->write( $qrCode );
		return 'data:image/png;base64,' . base64_encode( $result->getString() );
	}

	/**
	 *
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=bs-qr-code&page=Main0x20page'
				=> 'apihelp-bs-qr-code-example-bs-qr-code',
				'action=bs-qr-code&page=Main0x20page&revid=1'
				=> 'apihelp-bs-qr-code-example-bs-qr-code-with-param-revid',
				'action=bs-qr-code&page=Main0x20page&size=250'
				=> 'apihelp-bs-qr-code-example-bs-qr-code-with-param-size'
		];
	}
}
