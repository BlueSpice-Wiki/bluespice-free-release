<?php

namespace BlueSpice\Discovery\Rest;

use BlueSpice\Discovery\AttentionIndicatorFactory;
use MediaWiki\Rest\SimpleHandler;
use RequestContext;
use Wikimedia\ParamValidator\ParamValidator;

class AttentionIndicatorHandler extends SimpleHandler {

	/**
	 * @param AttentionIndicatorFactory $indicatorFactory
	 */
	public function __construct(
		private readonly AttentionIndicatorFactory $indicatorFactory
	) {
	}

	/**
	 * @return array
	 */
	public function execute() {
		$user = RequestContext::getMain()->getUser();
		$params = $this->getValidatedParams();
		$indicators = [];
		if ( $params['indicators'] ) {
			$indicators = explode( ',', $params['indicators'] );
			$indicators = array_map( 'trim', $indicators );
		}
		$res = [];
		foreach ( $this->indicatorFactory->getAll( $user ) as $indicator ) {
			if ( $indicators && !in_array( $indicator->getKey(), $indicators, true ) ) {
				continue;
			}
			$res[$indicator->getKey()] = $indicator->hasIndication();
		}

		return $res;
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'indicators' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'string'
			]
		];
	}
}
