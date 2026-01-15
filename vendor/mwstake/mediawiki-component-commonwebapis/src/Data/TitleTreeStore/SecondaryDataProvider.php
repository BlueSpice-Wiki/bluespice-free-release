<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\SecondaryDataProvider as TitleSecondaryDataProvider;
use MWStake\MediaWiki\Component\DataStore\Record;

class SecondaryDataProvider extends TitleSecondaryDataProvider {

	/**
	 * @inheritDoc
	 */
	public function extend( $dataSets ) {
		$extended = parent::extend( $dataSets );
		foreach ( $extended as $dataSet ) {
			$this->extend( $dataSet->get( TitleTreeRecord::CHILDREN ) );
		}
		return $extended;
	}

	/**
	 * @param Record $dataSet
	 * @param Title $title
	 *
	 * @return void
	 */
	protected function extendWithTitle( Record $dataSet, Title $title ) {
		parent::extendWithTitle( $dataSet, $title );
		if ( $title->getNamespace() === NS_MAIN ) {
			$dataSet->set( TitleTreeRecord::ID, ':' . $title->getDBkey() );
		} else {
			$dataSet->set( TitleTreeRecord::ID, $title->getPrefixedDBkey() );
		}
		$dataSet->set( TitleTreeRecord::PAGE_EXISTS, $title->exists() );
		if (
			(
				$dataSet->get( TitleTreeRecord::LOADED ) &&
				empty( $dataSet->get( TitleTreeRecord::CHILDREN ) )
			) ||
			!$title->hasSubpages()
		) {
			$dataSet->set( TitleTreeRecord::LEAF, true );
		}
	}
}
