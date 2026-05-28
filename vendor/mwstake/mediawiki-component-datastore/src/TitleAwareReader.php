<?php

namespace MWStake\MediaWiki\Component\DataStore;

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use UnexpectedValueException;

/**
 * Reader for stores returning representations of Title objects
 * Handles permission checking on its own
 */
abstract class TitleAwareReader extends Reader {

	/**
	 * @param array $dataSets
	 * @param int $total
	 * @param ITrimmer $trimmer
	 * @param string|null $queryId
	 * @return ResultSet
	 */
	protected function getResultSet( array $dataSets, int $total, ITrimmer $trimmer, ?string $queryId = null ) {
		if ( !( $trimmer instanceof PermissionTrimmer ) ) {
			return parent::getResultSet( $dataSets, $total, $trimmer, $queryId );
		}
		return new ResultSet(
			$dataSets,
			$trimmer->getTotal(),
			$trimmer->getContinue(),
			(bool)$trimmer->getContinue(),
			$queryId,
		);
	}

	/**
	 * @param ReaderParams $params
	 * @return ITrimmer|PermissionTrimmer
	 */
	protected function makeTrimmer( $params ): ITrimmer {
		$user = $this->context?->getUser() ?? RequestContext::getMain()->getUser();
		if ( !$user ) {
			// Very edge case
			throw new UnexpectedValueException( 'No context user found' );
		}
		return new PermissionTrimmer(
			MediaWikiServices::getInstance()->getTitleFactory(),
			MediaWikiServices::getInstance()->getPermissionManager(),
			$user,
			$params->getLimit(),
			$params->getContinueFrom(),
			$params->getStart()
		);
	}
}
