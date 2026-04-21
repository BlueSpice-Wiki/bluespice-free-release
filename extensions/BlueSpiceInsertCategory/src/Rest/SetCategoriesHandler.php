<?php

namespace BlueSpice\InsertCategory\Rest;

use BlueSpice\InsertCategory\CategoryManipulatorFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class SetCategoriesHandler extends SimpleHandler {

	/**
	 * @param CategoryManipulatorFactory $categoryManipulatorFactory
	 * @param TitleFactory $titleFactory
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct(
		private readonly CategoryManipulatorFactory $categoryManipulatorFactory,
		private readonly TitleFactory $titleFactory,
		private readonly WikiPageFactory $wikiPageFactory
	) {
	}

	/**
	 * @return true
	 * @throws HttpException
	 */
	public function execute() {
		$pageId = $this->getValidatedParams()['page_id'];
		$categories = $this->getValidatedBody()['categories'];

		$title = $this->titleFactory->newFromID( $pageId );
		if ( !$title || !$title->canExist() || !$title->exists() ) {
			throw new HttpException( 'Invalid page', 404 );
		}

		$categoryTitles = [];
		foreach ( $categories as $category ) {
			if ( !is_string( $category ) || empty( $category ) ) {
				throw new HttpException( 'Invalid category name', 400 );
			}
			$categoryTitles[] = $this->titleFactory->newFromText( $category, NS_CATEGORY );
		}

		$manipulator = $this->categoryManipulatorFactory->getManipulatorForContent(
			$this->wikiPageFactory->newFromTitle( $title )->getContent(),
			$title
		);
		if ( !$manipulator ) {
			throw new HttpException( 'No category manipulation supported for this content', 400 );
		}

		if ( !$manipulator->setCategories( $title, $categoryTitles, RequestContext::getMain()->getUser() ) ) {
			throw new HttpException( 'Failed to set categories', 500 );
		}
		return true;
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'categories' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_DEFAULT => [],
			]
		];
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'page_id' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	/**
	 * @return true
	 */
	public function needsWriteAccess() {
		return true;
	}
}
