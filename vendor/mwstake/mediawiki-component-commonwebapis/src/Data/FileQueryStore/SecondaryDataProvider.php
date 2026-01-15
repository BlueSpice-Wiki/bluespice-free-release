<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\FileQueryStore;

use MediaWiki\Context\RequestContext;
use MediaWiki\Language\Language;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\SecondaryDataProvider
	as TitleSecondaryDataProvider;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;

class SecondaryDataProvider extends TitleSecondaryDataProvider {

	/** @var TitleFactory */
	protected $titleFactory;
	/** @var Language */
	protected $language;
	/** @var PageProps */
	protected $pageProps;
	/** @var \RepoGroup */
	protected $repoGroup;
	/** @var RequestContext|null */
	protected $context;

	/**
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param PageProps $pageProps
	 * @param \RepoGroup $repoGroup
	 */
	public function __construct( $titleFactory, Language $language, PageProps $pageProps, \RepoGroup $repoGroup ) {
		$this->titleFactory = $titleFactory;
		$this->language = $language;
		$this->pageProps = $pageProps;
		$this->repoGroup = $repoGroup;
		$this->context = RequestContext::getMain();
	}

	/**
	 * @param array $dataSets
	 *
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function extend( $dataSets ) {
		$dataSets = parent::extend( $dataSets );
		foreach ( $dataSets as $dataSet ) {
			$title = $this->titleFromRecord( $dataSet );
			$dataSet->set( TitleRecord::PAGE_PREFIXED, $title->getPrefixedText() );
			$file = $this->repoGroup->getLocalRepo()->newFile( $title );

			$dataSet->set(
				FileRecord::FILE_TIMESTAMP_FORMATTED,
				$this->context->getLanguage()->userDate( $file->getTimestamp(), $this->context->getUser() )
			);
			$dataSet->set(
				FileRecord::FILE_SIZE,
				$this->context->getLanguage()->formatSize( $file->getSize() )
			);
			$dataSet->set(
				FileRecord::FILE_THUMBNAIL_URL,
				$file->createThumb( 40 )
			);
			$dataSet->set(
				FileRecord::FILE_THUMBNAIL_URL_PREVIEW,
				$file->createThumb( 120 )
			);
			$dataSet->set(
				FileRecord::FILE_MEDIATYPE,
				$file->getMediaType()
			);
			$dataSet->set(
				FileRecord::FILE_WIDTH,
				$file->getWidth()
			);
			$dataSet->set(
				FileRecord::FILE_HEIGHT,
				$file->getHeight()
			);
		}

		return $dataSets;
	}
}
