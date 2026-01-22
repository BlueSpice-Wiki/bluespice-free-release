<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler\Rest;

use Exception;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\TagFactory;
use MWStake\MediaWiki\Component\InputProcessor\IProcessor;
use MWStake\MediaWiki\Component\InputProcessor\ProcessorFactory;

class ListTagsHandler extends SimpleHandler {
	public function __construct(
		private readonly TagFactory $tagFactory,
		private readonly ProcessorFactory $processorFactory
	) {
	}

	/**
	 * @return Response|mixed
	 * @throws Exception
	 */
	public function execute() {
		$tags = $this->tagFactory->getAll();

		$result = [];
		foreach ( $tags as $tag ) {
			$paramValidators = $tag->getParamDefinition();
			foreach ( $paramValidators as &$validator ) {
				if ( $validator instanceof IProcessor ) {
					continue;
				}
				if ( !is_array( $validator ) || !isset( $validator['type'] ) ) {
					$validator = null;
					continue;
				}
				$validator = $this->processorFactory->createWithData( $validator['type'], $validator );
			}
			$paramValidators = array_filter( $paramValidators ?? [] );

			$tagSpec = $tag->getClientTagSpecification()?->jsonSerialize() ?? [];
			if ( isset( $tagSpec['formSpecification'] )
				&& isset( $tagSpec['formSpecification']['definition'] )
				&& isset( $tagSpec['formSpecification']['definition']['items'] )
			) {
				foreach ( $tagSpec['formSpecification']['definition']['items'] as &$item ) {
					if ( !isset( $item['labelAlign'] ) ) {
						$item['labelAlign'] = 'top';
					}
				}
			}

			$result[] = [
				'tags' => $tag->getTagNames(),
				'hasContent' => $tag->hasContent(),
				'paramDefinition' => $paramValidators,
				'clientSpecification' => $tagSpec
			];
		}

		return $this->getResponseFactory()->createJson( $result );
	}
}
