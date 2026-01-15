<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Rest\Handler;
use MediaWiki\Rest\Response;
use MWStake\MediaWiki\Component\DataStore\BucketedResultSet;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\ResultSet;
use Wikimedia\ParamValidator\ParamValidator;

abstract class QueryStore extends Handler {
	/** @var HookContainer */
	protected $hookContainer;

	/**
	 * @param HookContainer $hookContainer
	 */
	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @inheritDoc
	 */
	public function needsReadAccess() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function needsWriteAccess() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$store = $this->getStore();
		$readerParams = $this->getReaderParams();
		return $this->returnResult( $this->getResult( $store, $readerParams ) );
	}

	/**
	 * @return IStore
	 */
	abstract protected function getStore(): IStore;

	/**
	 * @return array
	 */
	protected function getStoreSpecificParams(): array {
		return [];
	}

	/**
	 * @return ReaderParams
	 */
	protected function getReaderParams(): ReaderParams {
		return new ReaderParams( [
			'query' => $this->getQuery(),
			'start' => $this->getOffset(),
			'limit' => $this->getLimit(),
			'filter' => $this->getFilter(),
			'sort' => $this->getSort()
		] );
	}

	/**
	 * @param IStore $store
	 * @param ReaderParams $readerParams
	 *
	 * @return ResultSet
	 */
	protected function getResult( IStore $store, ReaderParams $readerParams ): ResultSet {
		return $store->getReader()->read( $readerParams );
	}

	/**
	 * @param ResultSet $result
	 *
	 * @return Response
	 */
	protected function returnResult( ResultSet $result ): Response {
		$this->hookContainer->run( 'MWStakeCommonWebAPIsQueryStoreResult', [ $this, &$result ] );
		$contentType = $contentType ?? 'application/json';
		$responseData = [
			'results' => $result->getRecords(),
			'total' => $result->getTotal(),
		];
		if ( $result instanceof BucketedResultSet ) {
			$responseData['buckets'] = $result->getBuckets();
		}
		$response = new Response( $this->encodeJson( $responseData ) );
		$response->setHeader( 'Content-Type', $contentType );
		return $response;
	}

	/**
	 * @param array $data
	 *
	 * @return false|string
	 */
	protected function encodeJson( $data ) {
		return json_encode( $data, $this->getFormat() === 'jsonfm' ? JSON_PRETTY_PRINT : 0 );
	}

	/**
	 * @return array
	 */
	public function getParamSettings() {
		return array_merge( [
			'sort' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'string',
			],
			'filter' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'string',
			],
			'limit' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_DEFAULT => 25
			],
			'start' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_DEFAULT => 0
			],
			'format' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => [ 'json', 'jsonfm' ],
				ParamValidator::PARAM_DEFAULT => 'json'
			],
			'query' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => '',
				ParamValidator::PARAM_TYPE => 'string',
			],
		], $this->getStoreSpecificParams() );
	}

	/**
	 * @return int
	 */
	protected function getOffset(): int {
		return (int)$this->getValidatedParams()['start'];
	}

	/**
	 * @return int
	 */
	protected function getLimit(): int {
		return (int)$this->getValidatedParams()['limit'];
	}

	/**
	 * @return array
	 */
	protected function getFilter(): array {
		$validated = $this->getValidatedParams();
		if ( is_array( $validated ) && isset( $validated['filter'] ) ) {
			return json_decode( $validated['filter'], 1 );
		}
		return [];
	}

	/**
	 * @return array
	 */
	protected function getSort(): array {
		$validated = $this->getValidatedParams();
		if ( is_array( $validated ) && isset( $validated['sort'] ) ) {
			return json_decode( $validated['sort'], 1 );
		}
		return [];
	}

	/**
	 * @return string
	 */
	protected function getFormat(): string {
		return $this->getValidatedParams()['format'];
	}

	/**
	 * @return string
	 */
	protected function getQuery(): string {
		return $this->getValidatedParams()['query'];
	}
}
