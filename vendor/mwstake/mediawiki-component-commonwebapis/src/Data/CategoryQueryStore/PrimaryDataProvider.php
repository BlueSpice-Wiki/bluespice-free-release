<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\CategoryQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\PrimaryDataProvider as TitlePrimaryProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class PrimaryDataProvider extends TitlePrimaryProvider {

	/**
	 * @inheritDoc
	 */
	public function makeData( $params ) {
		$params = CategoryReaderParams::newFromOtherReaderParams( $params );
		$res = $this->db->select(
			[ 'mws_category_index', 'category' ],
			[
				'0 as mti_page_id',
				'\'\' as page_content_model',
				'cat_title as page_title',
				NS_CATEGORY . ' as page_namespace'
			],
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params )
		);

		foreach ( $res as $row ) {
			$this->appendRowToData( $row );
		}

		return $this->data;
	}

	/**
	 * @param ReaderParams $params
	 * @return array|string[]
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$query = $params->getQuery();
		$conds = [ 'mci_page_title = cat_title' ];
		if ( $query ) {
			$query = mb_strtolower( str_replace( '_', ' ', $query ) );
			$conds[] = 'mci_title' . $this->db->buildLike(
					$this->db->anyString(), $query, $this->db->anyString()
				);
		}

		return $conds;
	}
}
