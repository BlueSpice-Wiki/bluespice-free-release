<?php

namespace BlueSpice\InstanceStatus\Rest;

use BlueSpice\InstanceStatus\StatusReport;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MWException;
use Wikimedia\IPUtils;

class StatusCheckHandler extends SimpleHandler {

	/**
	 * @param StatusReport $statusReport
	 * @param ConfigFactory $configFactory
	 */
	public function __construct(
		private readonly StatusReport $statusReport,
		private readonly ConfigFactory $configFactory
	) {
	}

	/**
	 * @return Response
	 * @throws HttpException
	 */
	public function execute() {
		$config = $this->configFactory->makeConfig( 'bsg' );
		$clientIP = $this->getClientIP();
		if (
			!IPUtils::isValidRange( $config->get( 'InstanceStatusCheckAllowedIP' ) ) ||
			!IPUtils::isInRange( $clientIP, $config->get( 'InstanceStatusCheckAllowedIP' ) )
		) {
			throw new HttpException( 'permissiondenied', 401 );
		}
		return $this->getResponseFactory()->createJson(
			$this->statusReport->getReportForAPI()
		);
	}

	/**
	 * @return string
	 * @throws MWException
	 */
	private function getClientIP() {
		return RequestContext::getMain()->getRequest()->getIP();
	}

	/**
	 * @inheritDoc
	 * We perform access control via CIDR range
	 */
	public function needsReadAccess() {
		return false;
	}
}
