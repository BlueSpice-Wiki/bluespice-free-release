<?php

namespace BlueSpice\QrCode\DynamicFileDispatcher;

use MediaWiki\Permissions\Authority;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFileModule;

class QrCode implements IDynamicFileModule {

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	public function getFile( array $params ): ?IDynamicFile {
		return new QrCodeImage(
			$this->titleFactory,
			$params['pagename'] ?? $this->titleFactory->newMainPage()->getPrefixedText(),
			$params['query'] ?? '',
			isset( $params['size'] ) ? (int)$params['size'] : 100
		);
	}

	/**
	 * @inheritDoc
	 */
	public function isAuthorized( Authority $user, array $params ): bool {
		return true;
	}
}
