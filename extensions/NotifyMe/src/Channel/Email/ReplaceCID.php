<?php

namespace MediaWiki\Extension\NotifyMe\Channel\Email;

use MediaWiki\Hook\UserMailerTransformMessageHook;

class ReplaceCID implements UserMailerTransformMessageHook {
	private const EOL = "\r\n";

	/**
	 * @param MailContentProvider $mailContentProvider
	 */
	public function __construct(
		private readonly MailContentProvider $mailContentProvider
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onUserMailerTransformMessage( $to, $from, &$subject, &$headers, &$body, &$error ) {
		$images = $this->mailContentProvider->getImages();
		if ( !$images || !$this->isHtml( $headers ) ) {
			return;
		}

		$inlineParts = [];
		foreach ( $images as $cid => $file ) {
			if ( !str_contains( $body, "cid:$cid" ) ) {
				continue;
			}

			$storagePath = $file->getLocalRefPath();
			if ( !is_string( $storagePath ) || !file_exists( $storagePath ) ) {
				continue;
			}

			$imageData = file_get_contents( $storagePath );
			if ( !$imageData ) {
				continue;
			}

			$filename = method_exists( $file, 'getName' ) ? $file->getName() : basename( $storagePath );
			$inlineParts[] = [
				'cid' => $cid,
				'mimeType' => $file->getMimeType(),
				'filename' => $filename,
				'body' => chunk_split( base64_encode( $imageData ), 76, self::EOL ),
			];
		}

		if ( !$inlineParts ) {
			return;
		}

		$boundary = 'notifyme-related-' . md5( uniqid( '', true ) );

		$mimeBody = [];
		$mimeBody[] = '--' . $boundary;
		$mimeBody[] = 'Content-Type: text/html; charset=UTF-8';
		$mimeBody[] = 'Content-Transfer-Encoding: 8bit';
		$mimeBody[] = '';
		$mimeBody[] = $body;

		foreach ( $inlineParts as $part ) {
			$mimeBody[] = '--' . $boundary;
			$mimeBody[] = 'Content-Type: ' . $part['mimeType'] . '; name="' . $part['filename'] . '"';
			$mimeBody[] = 'Content-Transfer-Encoding: base64';
			$mimeBody[] = 'Content-Disposition: inline; filename="' . $part['filename'] . '"';
			$mimeBody[] = 'Content-ID: <' . $part['cid'] . '>';
			$mimeBody[] = '';
			$mimeBody[] = $part['body'];
		}

		$mimeBody[] = '--' . $boundary . '--';
		$body = implode( self::EOL, $mimeBody );

		$this->unsetHeaderCaseInsensitive( $headers, 'Content-Type' );
		$this->unsetHeaderCaseInsensitive( $headers, 'Content-Transfer-Encoding' );
		$headers['MIME-Version'] = '1.0';
		$headers['Content-Type'] = 'multipart/related; boundary="' . $boundary . '"';
	}

	/**
	 * @param array $headers
	 *
	 * @return bool
	 */
	private function isHtml( array $headers ): bool {
		return str_contains(
			$headers['Content-type'] ?? $headers['Content-Type'] ?? 'text/plain',
			'text/html'
		);
	}

	/**
	 * @param array &$headers
	 * @param string $headerName
	 *
	 * @return void
	 */
	private function unsetHeaderCaseInsensitive( array &$headers, string $headerName ): void {
		foreach ( $headers as $name => $_value ) {
			if ( strcasecmp( $name, $headerName ) === 0 ) {
				unset( $headers[$name] );
			}
		}
	}
}
