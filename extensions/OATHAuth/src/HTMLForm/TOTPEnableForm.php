<?php

namespace MediaWiki\Extension\OATHAuth\HTMLForm;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use MediaWiki\Config\ConfigException;
use MediaWiki\Extension\OATHAuth\Key\TOTPKey;
use MediaWiki\Html\Html;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Status\Status;
use MWException;

class TOTPEnableForm extends OATHAuthOOUIHTMLForm {
	/**
	 * @param array|bool|Status|string $submitResult
	 * @return string
	 */
	public function getHTML( $submitResult ) {
		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.oath.styles' );
		$out->addModules( 'ext.oath' );

		return parent::getHTML( $submitResult );
	}

	/**
	 * Add content to output when operation was successful
	 */
	public function onSuccess() {
		$this->getOutput()->addWikiMsg( 'oathauth-validatedoath' );
	}

	/**
	 * @return array
	 */
	protected function getDescriptors() {
		$keyData = $this->getRequest()->getSessionData( 'oathauth_totp_key' ) ?? [];
		$key = TOTPKey::newFromArray( $keyData );
		if ( !$key instanceof TOTPKey ) {
			$key = TOTPKey::newFromRandom();
			$this->getRequest()->setSessionData(
				'oathauth_totp_key',
				$key->jsonSerialize()
			);
		}

		$secret = $key->getSecret();
		$issuer = $this->oathUser->getIssuer();
		$account = $this->oathUser->getAccount();
		$label = "{$issuer}:{$account}";
		$qrcodeUrl = "otpauth://totp/"
			. rawurlencode( $label )
			. "?secret="
			. rawurlencode( $secret )
			. "&issuer="
			. rawurlencode( $issuer );

		$qrCode = Builder::create()
			->writer( new SvgWriter() )
			->writerOptions( [ SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true ] )
			->data( $qrcodeUrl )
			->encoding( new Encoding( 'UTF-8' ) )
			->errorCorrectionLevel( ErrorCorrectionLevel::High )
			->roundBlockSizeMode( RoundBlockSizeMode::None )
			->size( 256 )
			->margin( 0 )
			->build();

		$now = wfTimestampNow();
		$recoveryCodes = $this->getScratchTokensForDisplay( $key );
		$this->getOutput()->addJsConfigVars( 'oathauth-recoverycodes', $this->createTextList( $recoveryCodes ) );

		// messages used: oathauth-step1, oathauth-step2, oathauth-step3, oathauth-step4
		return [
			'app' => [
				'type' => 'info',
				'default' => $this->msg( 'oathauth-step1-test' )->parse(),
				'raw' => true,
				'section' => 'step1',
			],
			'qrcode' => [
				'type' => 'info',
				'default' =>
					$this->msg( 'oathauth-step2-qrcode' )->escaped() . '<br/>'
					. Html::element( 'img', [
						'src' => $qrCode->getDataUri(),
						'alt' => $this->msg( 'oathauth-qrcode-alt' ),
						'width' => 256,
						'height' => 256,
					] ),
				'raw' => true,
				'section' => 'step2',
			],
			'manual' => [
				'type' => 'info',
				'label-message' => 'oathauth-step2alt',
				'default' =>
					'<strong>' . $this->msg( 'oathauth-secret' )->escaped() . '</strong><br/>'
					. '<kbd>' . $this->getSecretForDisplay( $key ) . '</kbd><br/>'
					. '<strong>' . $this->msg( 'oathauth-account' )->escaped() . '</strong><br/>'
					. htmlspecialchars( $label ) . '<br/><br/>',
				'raw' => true,
				'section' => 'step2',
			],
			'scratchtokens' => [
				'type' => 'info',
				'default' =>
					'<strong>' . $this->msg( 'oathauth-recoverycodes-important' )->escaped() . '</strong><br/>' .
					$this->msg( 'oathauth-recoverycodes' )->escaped() . '<br/><br/>' .
					$this->msg( 'rawmessage' )->rawParams(
						$this->msg(
							'oathauth-recoverytokens-createdat',
							$this->getLanguage()->userTimeAndDate( $now, $this->oathUser->getUser() )
						)->parse()
						. $this->msg( 'word-separator' )->escaped()
						. $this->msg( 'parentheses' )->rawParams( wfTimestamp( TS_ISO_8601, $now ) )->escaped()
					) . '<br/>' .
					$this->createResourceList( $recoveryCodes ) . '<br/>' .
					'<strong>' . $this->msg( 'oathauth-recoverycodes-neveragain' )->escaped() . '</strong><br/>' .
					$this->createCopyButton() .
					$this->createDownloadLink( $recoveryCodes ),
				'raw' => true,
				'section' => 'step3',
			],
			'token' => [
				'type' => 'text',
				'default' => '',
				'label-message' => 'oathauth-entertoken',
				'name' => 'token',
				'section' => 'step4',
				'dir' => 'ltr',
				'autocomplete' => 'one-time-code',
				'spellcheck' => false,
			]
		];
	}

	/**
	 * @param array $resources
	 * @return string
	 */
	private function createResourceList( $resources ) {
		$resourceList = '';
		foreach ( $resources as $resource ) {
			$resourceList .= Html::rawElement( 'li', [], Html::rawElement( 'kbd', [], $resource ) );
		}
		return Html::rawElement( 'ul', [], $resourceList );
	}

	/**
	 * @param array $items
	 *
	 * @return string
	 */
	private function createTextList( $items ) {
		return "* " . implode( "\n* ", $items );
	}

	private function createDownloadLink( array $scratchTokensForDisplay ): string {
		$icon = Html::element( 'span', [
			'class' => [ 'mw-oathauth-recoverycodes-download-icon', 'cdx-button__icon' ],
			'aria-hidden' => 'true',
		] );
		return Html::rawElement(
			'a',
			[
				'href' => 'data:text/plain;charset=utf-8,'
					// https://bugzilla.mozilla.org/show_bug.cgi?id=1895687
					. rawurlencode( implode( PHP_EOL, $scratchTokensForDisplay ) ),
				'download' => 'recovery-codes.txt',
				'class' => [
					'mw-oathauth-recoverycodes-download',
					'cdx-button', 'cdx-button--fake-button', 'cdx-button--fake-button--enabled',
				],
			],
			$icon . $this->msg( 'oathauth-recoverycodes-download' )->escaped()
		);
	}

	private function createCopyButton(): string {
		return Html::rawElement( 'button', [
			'class' => 'cdx-button mw-oathauth-recoverycodes-copy-button',
			'type' => 'button',
		], Html::element( 'span', [
			'class' => 'cdx-button__icon',
			'aria-hidden' => 'true',
		] ) . $this->msg( 'oathauth-recoverycodes-copy' )->escaped()
		);
	}

	/**
	 * Retrieve the current secret for display purposes
	 *
	 * The characters of the token are split in groups of 4
	 *
	 * @param TOTPKey $key
	 * @return string
	 */
	protected function getSecretForDisplay( TOTPKey $key ) {
		return $this->tokenFormatterFunction( $key->getSecret() );
	}

	/**
	 * Retrieve current recovery codes for display purposes
	 *
	 * The characters of the token are split in groups of 4
	 *
	 * @param TOTPKey $key
	 * @return string[]
	 */
	protected function getScratchTokensForDisplay( TOTPKey $key ) {
		return array_map( [ $this, 'tokenFormatterFunction' ], $key->getScratchTokens() );
	}

	/**
	 * Formats a key or recovery code by creating groups of 4 separated by space characters
	 *
	 * @param string $token Token to format
	 * @return string The token formatted for display
	 */
	private function tokenFormatterFunction( $token ) {
		return implode( ' ', str_split( $token, 4 ) );
	}

	/**
	 * @param array $formData
	 * @return array|bool
	 * @throws ConfigException
	 * @throws MWException
	 */
	public function onSubmit( array $formData ) {
		$keyData = $this->getRequest()->getSessionData( 'oathauth_totp_key' ) ?? [];
		$key = TOTPKey::newFromArray( $keyData );
		if ( !$key instanceof TOTPKey ) {
			return [ 'oathauth-invalidrequest' ];
		}

		if ( $key->isScratchToken( $formData['token'] ) ) {
			// A recovery code is not allowed for enrollment
			LoggerFactory::getInstance( 'authentication' )->info(
				'OATHAuth {user} attempted to enable 2FA using a recovery code from {clientip}', [
					'user' => $this->getUser()->getName(),
					'clientip' => $this->getRequest()->getIP(),
				]
			);
			return [ 'oathauth-noscratchforvalidation' ];
		}
		if ( !$key->verify( [ 'token' => $formData['token'] ], $this->oathUser ) ) {
			LoggerFactory::getInstance( 'authentication' )->info(
				'OATHAuth {user} failed to provide a correct token while enabling 2FA from {clientip}', [
					'user' => $this->getUser()->getName(),
					'clientip' => $this->getRequest()->getIP(),
				]
			);
			return [ 'oathauth-failedtovalidateoath' ];
		}

		$this->getRequest()->setSessionData( 'oathauth_totp_key', null );
		$this->oathRepo->createKey(
			$this->oathUser,
			$this->module,
			$key->jsonSerialize(),
			$this->getRequest()->getIP()
		);

		return true;
	}
}
