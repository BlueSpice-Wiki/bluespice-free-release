<?php

namespace MWStake\MediaWiki\Component\DataStore;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

abstract class Reader implements IReader {

	/**
	 * How long to cache results for.
	 * This cache is meant to last for the user's duration of the page view,
	 * to ease pagination, not for long term caching
	 */
	protected const CACHE_TTL = ExpirationAwareness::TTL_MINUTE * 2;

	/**
	 * @var IContextSource
	 */
	protected $context = null;
	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var \ObjectCacheFactory
	 */
	protected $cacheFactory = null;

	/**
	 * @param IContextSource|null $context
	 * @param Config|null $config
	 * @param \ObjectCacheFactory|null $cacheFactory
	 */
	public function __construct(
		?IContextSource $context = null, ?Config $config = null,
		?\ObjectCacheFactory $cacheFactory = null
	) {
		$this->context = $context;
		if ( $this->context === null ) {
			$this->context = RequestContext::getMain();
		}

		$this->config = $config;
		if ( $this->config === null ) {
			$this->config = MediaWikiServices::getInstance()->getMainConfig();
		}

		$this->cacheFactory = $cacheFactory;
		if ( $this->cacheFactory === null ) {
			$this->cacheFactory = MediaWikiServices::getInstance()->getObjectCacheFactory();
		}
	}

	/**
	 * @return User
	 */
	protected function getUser() {
		return $this->context->getUser();
	}

	/**
	 * @return Title
	 */
	protected function getTitle() {
		return $this->context->getTitle();
	}

	/**
	 * @param ReaderParams $params
	 * @return ResultSet
	 */
	public function read( $params ) {
		$queryId = $this->getQueryId( $params );
		$dataSets = null;
		$buckets = null;
		if ( $queryId && !$params->getDisableCache() ) {
			$dataSets = $this->tryGetFromCache( $queryId );
			$buckets = $this->tryGetBucketsFromCache( $queryId );
		}
		if ( !$dataSets ) {
			$primaryDataProvider = $this->makePrimaryDataProvider( $params );
			$dataSets = $primaryDataProvider->makeData( $params );
			if ( $primaryDataProvider instanceof IBucketProvider ) {
				$buckets = $primaryDataProvider->getBuckets();
				if ( $buckets && $queryId ) {
					$this->cacheBuckets( $queryId, $buckets );
				}
			}
			$this->cacheResults( $queryId, $dataSets );
		}
		$this->preProcessRawData( $dataSets, $params );

		$filterer = $this->makeFilterer( $params );
		$dataSets = $filterer->filter( $dataSets );
		$total = count( $dataSets );

		$sorter = $this->makeSorter( $params );
		$dataSets = $sorter->sort(
			$dataSets,
			$this->getSchema()->getUnsortableFields()
		);

		$trimmer = $this->makeTrimmer( $params );
		$dataSets = $trimmer->trim( $dataSets );

		$secondaryDataProvider = $this->makeSecondaryDataProvider();
		if ( $secondaryDataProvider instanceof ISecondaryDataProvider ) {
			$dataSets = $secondaryDataProvider->extend( $dataSets );
		}

		$resultSet = $this->getResultSet( $dataSets, $total, $trimmer, $queryId );
		if ( $buckets ) {
			$resultSet->setBuckets( $buckets );
		}

		return $resultSet;
	}

	/**
	 * @param array &$dataSets
	 * @param ReaderParams $params
	 * @return void
	 */
	protected function preProcessRawData( array &$dataSets, $params ): void {
		// NOOP
	}

	/**
	 * @param array $dataSets
	 * @param int $total
	 * @param ITrimmer $trimmer
	 * @param string $queryId
	 * @return ResultSet
	 */
	protected function getResultSet( array $dataSets, int $total, ITrimmer $trimmer, string $queryId ) {
		if ( $trimmer instanceof LimitContinueTrimmer ) {
			return new ResultSet( $dataSets, $total, $trimmer->getContinue(), false, $queryId );
		}
		return new ResultSet( $dataSets, $total, [], false, $queryId );
	}

	/**
	 * @param ReaderParams $params
	 * @return IPrimaryDataProvider
	 */
	abstract protected function makePrimaryDataProvider( $params );

	/**
	 * @param ReaderParams $params
	 * @return Filterer
	 */
	protected function makeFilterer( $params ) {
		return new Filterer( $params->getFilter() );
	}

	/**
	 * @param ReaderParams $params
	 * @return Sorter
	 */
	protected function makeSorter( $params ) {
		return new Sorter( $params->getSort() );
	}

	/**
	 * @param ReaderParams $params
	 * @return ITrimmer
	 */
	protected function makeTrimmer( $params ) {
		return new LimitContinueTrimmer(
			$params->getLimit(),
			$params->getContinueFrom(),
			$params->getStart()
		);
	}

	/**
	 * @return ISecondaryDataProvider|null to skip
	 */
	abstract protected function makeSecondaryDataProvider();

	/**
	 * @param string $queryId
	 * @return array|null
	 */
	protected function tryGetFromCache( string $queryId ): ?array {
		// Due to issues with the cache invalidation, we disable this code
		// But we keep it for improvement in the next release
		return null;

/* 		$cache = $this->cacheFactory->getLocalServerInstance();
		$key = $cache->makeKey( 'datastore', 'reader', 'query', $queryId );
		$data = $cache->get( $key );
		if ( !is_array( $data ) ) {
			return null;
		}
		return $data; */
	}

	/**
	 * @param string $hash
	 * @param array $dataSets
	 * @return void
	 */
	protected function cacheResults( string $hash, array $dataSets ): void {
		$cache = $this->cacheFactory->getLocalServerInstance();
		$key = $cache->makeKey( 'datastore', 'reader', 'query', $hash );
		$cache->set( $key, $dataSets, static::CACHE_TTL );
	}

	/**
	 * @param ReaderParams $params
	 * @return string
	 */
	protected function getQueryId( ReaderParams $params ): string {
		return md5( get_class( $this ) . $params->getHash() );
	}

	/**
	 * @param string $queryId
	 * @param array $buckets
	 * @return void
	 */
	protected function cacheBuckets( string $queryId, array $buckets ) {
		$cache = $this->cacheFactory->getLocalServerInstance();
		$key = $cache->makeKey( 'datastore', 'reader', 'buckets', $queryId );
		$cache->set( $key, $buckets, static::CACHE_TTL );
	}

	/**
	 * @param string $queryId
	 * @return array|null
	 */
	protected function tryGetBucketsFromCache( string $queryId ) {
		// Due to issues with the cache invalidation, we disable this code
		// But we keep it for improvement in the next release
		return null;

/* 		$cache = $this->cacheFactory->getLocalServerInstance();
		$key = $cache->makeKey( 'datastore', 'reader', 'buckets', $queryId );
		$data = $cache->get( $key );
		if ( !is_array( $data ) ) {
			return null;
		}
		return $data; */
	}

}
