<?php

namespace BlueSpice\InsertCategory;

use BlueSpice\InsertCategory\CategoryManipulator\CategorySlotManipulator;
use BlueSpice\InsertCategory\CategoryManipulator\WikitextCategoryManipulator;
use MediaWiki\Content\Content;
use MediaWiki\Content\WikitextContent;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Title\Title;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory\ObjectFactory;

class CategoryManipulatorFactory {

	/** @var LoggerInterface */
	private readonly LoggerInterface $logger;

	public function __construct(
		private readonly HookContainer $hookContainer,
		private readonly ObjectFactory $objectFactory
	) {
		$this->logger = LoggerFactory::getInstance( 'BlueSpiceInsertCategory.CategoryManipulator' );
	}

	/**
	 * @param Content $content
	 * @param Title $page
	 * @return ICategoryManipulator|null
	 */
	public function getManipulatorForContent( Content $content, Title $page ): ?ICategoryManipulator {
		if ( !$content->getContentHandler()->supportsCategories() ) {
			return null;
		}
		$manipulator = $content instanceof WikitextContent ?
			$this->createWikitextContentManipulator() :
			$this->createCategorySlotManipulator();

		$this->hookContainer->run(
			'BlueSpiceInsertCategoryGetCategoryManipulator',
			[
				'content' => $content,
				'page' => $page,
				'manipulator' => &$manipulator,
			]
		);
		if ( $manipulator instanceof LoggerAwareInterface ) {
			$manipulator->setLogger( $this->logger );
		}
		return $manipulator;
	}

	/**
	 * @return ICategoryManipulator
	 */
	private function createWikitextContentManipulator(): ICategoryManipulator {
		return $this->objectFactory->createObject( [
			'class' => WikitextCategoryManipulator::class,
			'services' => [ 'WikiPageFactory', 'RevisionLookup', 'TitleFactory' ]
		] );
	}

	/**
	 * @return ICategoryManipulator
	 */
	private function createCategorySlotManipulator(): ICategoryManipulator {
		return $this->objectFactory->createObject( [
			'class' => CategorySlotManipulator::class,
			'services' => [ 'WikiPageFactory', 'RevisionLookup' ]
		] );
	}
}
