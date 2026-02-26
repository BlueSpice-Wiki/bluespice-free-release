<?php

namespace BlueSpice\InsertCategory\Content;

use InvalidArgumentException;
use MediaWiki\Content\JsonContent;
use MediaWiki\Title\Title;

class CategoryContent extends JsonContent {

	/**
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text = '', $modelId = 'category_storage' ) {
		if ( !$text ) {
			$text = json_encode( [ 'categories' => [] ] );
		}
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		$data = $this->getData();
		if ( !$data->isOK() ) {
			return [];
		}
		$value = $data->getValue();
		if ( !is_object( $value ) || empty( $value->categories ) ) {
			return [];
		}
		return $value->categories;
	}

	/**
	 * @param Title[] $categories
	 * @return $this
	 */
	public function setCategories( array $categories ): static {
		$toSet = [];
		foreach ( $categories as $category ) {
			if ( $category instanceof Title ) {
				$this->assertCategoryTitle( $category );
				$toSet[] = $category->getText();
			}
		}
		return $this->doSetCategories( $toSet );
	}

	/**
	 * @param Title $category
	 * @return $this
	 */
	public function removeCategory( Title $category ): static {
		$this->assertCategoryTitle( $category );
		$categories = $this->getCategories();
		if ( in_array( $category->getText(), $categories, true ) ) {
			$categories = array_diff( $categories, [ $category->getText() ] );
			return $this->doSetCategories( $categories );
		}

		return $this;
	}

	/**
	 * @param Title $category
	 * @return $this
	 */
	public function addCategory( Title $category ): static {
		$this->assertCategoryTitle( $category );
		$categories = $this->getCategories();
		$categories[] = $category->getText();
		return $this->doSetCategories( $categories );
	}

	/**
	 * @param Title $category
	 * @return void
	 */
	private function assertCategoryTitle( Title $category ): void {
		if ( $category->getNamespace() !== NS_CATEGORY ) {
			throw new InvalidArgumentException( 'Title must be in the CATEGORY namespace.' );
		}
	}

	/**
	 * @param array $categoryNames
	 * @return $this
	 */
	private function doSetCategories( array $categoryNames ): static {
		$this->mText = json_encode( [ 'categories' => array_values( array_unique( $categoryNames ) ) ] );
		$this->jsonParse = null;
		return $this;
	}
}
