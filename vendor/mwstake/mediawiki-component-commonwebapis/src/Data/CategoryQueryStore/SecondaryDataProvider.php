<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\CategoryQueryStore;

use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\SecondaryDataProvider as TitleSecondaryDataProvider;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;
use MWStake\MediaWiki\Component\DataStore\Record;

class SecondaryDataProvider extends TitleSecondaryDataProvider {

	/**
	 * @param Record $dataSet
	 * @param Title $title
	 *
	 * @return void
	 */
	protected function extendWithTitle( Record $dataSet, Title $title ) {
		parent::extendWithTitle( $dataSet, $title );
		$dataSet->set( TitleRecord::PAGE_ID, $title->getArticleID() );
		$dataSet->set( TitleRecord::PAGE_EXISTS, $title->exists() );
	}
}
