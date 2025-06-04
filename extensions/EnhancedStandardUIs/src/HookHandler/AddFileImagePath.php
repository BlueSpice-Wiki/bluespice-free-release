<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\CommonWebAPIs\Hook\MWStakeCommonWebAPIsQueryStoreResultHook;
use MWStake\MediaWiki\Component\CommonWebAPIs\Rest\FileQueryStore;
use MWStake\MediaWiki\Component\DataStore\ResultSet;
use RepoGroup;

class AddFileImagePath implements MWStakeCommonWebAPIsQueryStoreResultHook {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/**
	 * @param TitleFactory $titleFactory
	 * @param RepoGroup $repoGroup
	 */
	public function __construct( TitleFactory $titleFactory, RepoGroup $repoGroup ) {
		$this->titleFactory = $titleFactory;
		$this->repoGroup = $repoGroup;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonWebAPIsQueryStoreResult( $store, &$result ) {
		if ( !( $store instanceof FileQueryStore ) ) {
			return;
		}
		$data = $result->getRecords();
		foreach ( $data as $record ) {
			$title = $this->titleFactory->newFromText( 'File:' . $record->get( 'title' ) );
			$file = $this->repoGroup->getLocalRepo()->newFile( $title );
			if ( $file ) {
				$record->set( 'fileUrl', $file->getUrl() );
			}
		}

		$result = new ResultSet( $data, $result->getTotal() );
	}
}
