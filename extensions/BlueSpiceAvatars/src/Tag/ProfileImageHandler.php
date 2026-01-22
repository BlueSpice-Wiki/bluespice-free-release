<?php

namespace BlueSpice\Avatars\Tag;

use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage;
use BlueSpice\RendererFactory;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use RuntimeException;

class ProfileImageHandler implements ITagHandler {

	public function __construct(
		private readonly DynamicFileDispatcherFactory $dfdFactory,
		private readonly RendererFactory $rendererFactory,
		private readonly UserFactory $userFactory
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		if ( $params['raw'] ) {
			return $this->handleRaw( $params );
		}
		$user = $this->userFactory->newFromName( $params['username'] );
		if ( !$user ) {
			$msg = Message::newFromKey(
				'bs-avatars-tag-profileimage-error-invalidusername'
			);
			throw new RuntimeException( $msg );
		}
		$params = [
			UserImage::PARAM_HEIGHT => $params['height'],
			UserImage::PARAM_WIDTH => $params['width'],
			UserImage::PARAM_USER => $user,
			UserImage::PARAM_CLASS => 'bs-avatars-userimage-tag'
		];
		return $this->rendererFactory->get(
			'userimage',
			new Params( $params )
		)->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function handleRaw( array $params ) {
		$url = $this->dfdFactory->getUrl( 'userprofileimage', [
			'username' => $params['username'],
			'width' => $params['width'],
			'height' => $params['height']
		] );

		return Html::element( 'img', [
			'src' => $url,
			'alt' => Message::newFromKey(
				'bs-avatars-tag-userimage-img-alt',
				$params['username']
			)->text(),
			'class' => 'bs-avatars-userimage-tag',
			'width' => $params['width'],
			'height' => $params['height']
		] );
	}
}
