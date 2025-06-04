<?php

namespace MediaWiki\Extension\PDFCreator\Backend;

use GuzzleHttp\Client;
use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IExportBackend;
use MediaWiki\Extension\PDFCreator\Utility\ExportResources;
use MediaWiki\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class OpenHtml2Pdf implements IExportBackend {

	/** @var Config */
	private $config;

	/** @var string */
	private $rendererUrl;

	/** @var string */
	private $uploadUrl;

	/** @var string */
	private $workspaceDirectory;

	/** @var Client */
	private $guzzle;

	/** @var LoggerInterface */
	private $logger;

	/**
	 * @param Config $config
	 * @param LoggerInterface|null $logger
	 */
	public function __construct( Config $config, ?LoggerInterface $logger = null ) {
		$this->config = $config;

		if ( $logger instanceof LoggerInterface === false ) {
			$logger = LoggerFactory::getInstance( 'PDFCreator' );
		}
		$this->setLogger( $logger );

		$uploadDirectory = $config->get( 'UploadDirectory' );
		$this->workspaceDirectory = $this->ensureFileSystemPath( "$uploadDirectory/cache/PDFCreator" );

		$serviceUrl = $config->get( 'PDFCreatorOpenHtml2PdfServiceUrl' );
		$this->rendererUrl = "$serviceUrl/RenderPDF";
		$this->uploadUrl = "$serviceUrl/UploadAsset";

		$this->guzzle = new Client();
	}

	/**
	 * @param ExportResources $resources
	 * @param array $params
	 * @return string
	 */
	public function create( ExportResources $resources, array $params = [] ): string {
		$token = md5( $resources->getHtml() );

		# Upload all images
		$this->doUpload( $token, $resources->getImagePaths(), 'images' );

		# Upload all attachments
		$this->doUpload( $token, $resources->getAttachmentPaths(), 'attachments' );

		# Upload all stylesheets and fonts
		$this->doUpload( $token, $resources->getStylesheetPaths(), 'stylesheets' );

		# Upload the document
		$tmpHtmlFilename = "{$this->workspaceDirectory}/{$token}.html";
		file_put_contents( $tmpHtmlFilename, $resources->getHtml() );

		$this->doUpload( $token, [ $tmpHtmlFilename ] );
		if ( !isset( $params['debug'] ) ) {
			unlink( $tmpHtmlFilename );
		}

		$postData = $this->getInitialRendererPostData( $token );

		$response = $this->guzzle->request( 'POST', $this->rendererUrl, [
			'form_params' => $postData
		] );
		$status = $response->getStatusCode();
		if ( $status !== 200 ) {
			echo "Failed to create PDF\n";
		}
		$body = $response->getBody();

		return $body;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function ensureFileSystemPath( string $path ): string {
		if ( !file_exists( $path ) ) {
			mkdir( $path, 0755, true );
		}
		return $path;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param string $token
	 * @param array $files
	 * @param string $type
	 * @return void
	 */
	private function doUpload( string $token, array $files, string $type = '' ): void {
		if ( empty( $files ) ) {
			return;
		}
		$postData = $this->getInitiaUploadPostData( $token, $type );

		foreach ( $files as $name => $path ) {
			if ( !file_exists( $path ) ) {
				echo "Missing $type/$path\n";
				continue;
			}

			if ( is_string( $name ) ) {
				$filename = $name;
			} else {
				// Should be used for html only.
				// File arrays should have a name => path strucure
				$filename = basename( $path );
			}
			echo "Uploading $type/$filename\n";

			$postData[] = [
				'name' => $filename,
				'contents' => file_get_contents( $path ),
				'filename' => $filename
			];
			$postData[] = [
				'name' => "{$filename}_name",
				'contents' => $filename
			];
		}
		$uploadUrl = $this->uploadUrl;

		$response = $this->guzzle->request( 'POST', $uploadUrl, [
			'multipart' => $postData
		] );
		$body = $response->getBody();
		$json = json_decode( $body, true );
		if ( $json['success'] !== true ) {
			// TODO: Handle error
			echo "Failed to upload $type\n";
		} else {
			"Uplad sucessfully $type\n";
		}

		echo json_encode( $json, JSON_PRETTY_PRINT ) . "\n";
	}

	/**
	 * @param string $token
	 * @return array
	 */
	private function getInitialRendererPostData( string $token ): array {
		return [
			'wikiId' => 'html2pdftest',
			'documentToken' => $token,
			'debug' => 'true'
		];
	}

	/**
	 * @param string $token
	 * @param string $type
	 * @return array
	 */
	private function getInitiaUploadPostData( string $token, string $type ): array {
		return [
			[
				'name' => 'wikiId',
				'contents' => 'html2pdftest'
			],
			[
				'name' => 'documentToken',
				'contents' => $token
			],
			[
				'name' => 'fileType',
				'contents' => $type
			]
		];
	}
}
