<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler\Rest;

use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\GenericTagHandler\TagFactory;
use Wikimedia\ParamValidator\ParamValidator;

class RenderTagHandler extends SimpleHandler {

	/**
	 * @param TagFactory $tagFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly TagFactory $tagFactory,
		private readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @return Response|mixed
	 */
	public function execute() {
		$tags = $this->tagFactory->getAll();
		$requestedTag = $this->getValidatedParams()['tag'];
		$bodyParams = $this->getValidatedBody();

		$tagToHandle = null;
		foreach ( $tags as $tag ) {
			$names = $tag->getTagNames();
			if ( in_array( $requestedTag, $names ) ) {
				$tagToHandle = $tag;
				break;
			}
		}
		if ( !$tagToHandle ) {
			return $this->getResponseFactory()->createJson( [
				'error' => 'Tag not found',
			], 404 );
		}
		$title = $this->titleFactory->newFromText( $bodyParams['contextTitle'] );
		if ( !$title ) {
			return $this->getResponseFactory()->createJson( [
				'error' => 'Invalid context title',
			], 400 );
		}
		$renderer = $this->tagFactory->makeTagRendererForTag( $tagToHandle );
		$result = $renderer->render( $bodyParams['input'], $bodyParams['args'], $title );

		return $this->getResponseFactory()->createJson( [
			'html' => $result,
			'rlModules' => $tagToHandle->getResourceLoaderModules()
		] );
	}

	/** @inheritDoc */
	public function getParamSettings() {
		return [
			'tag' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string',
			],
		];
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'contextTitle' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string',
			],
			'input' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '',
			],
			'args' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_DEFAULT => [],
			],
		];
	}
}
