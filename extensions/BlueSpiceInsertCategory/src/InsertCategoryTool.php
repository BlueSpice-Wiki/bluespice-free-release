<?php

namespace BlueSpice\InsertCategory;

use MediaWiki\Content\WikitextContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;

class InsertCategoryTool extends Literal {

	/**
	 *
	 * @var MediaWikiServices
	 */
	private $services = null;

	/**
	 *
	 * @var bool
	 */
	private $btnDisabled = false;

	/**
	 *
	 * @var PermissionManger
	 */
	private $permissionManager = null;

	/** @var WikiPageFactory */
	private $wikiPageFactory = null;

	/**
	 *
	 */
	public function __construct() {
		$this->services = MediaWikiServices::getInstance();
		$this->permissionManager = $this->services->getPermissionManager();
		$this->wikiPageFactory = $this->services->getWikiPageFactory();

		/** @var RequestContext */
		$context = RequestContext::getMain();
		$user = $context->getUser();

		/** @var Title */
		$title = $context->getTitle();

		$this->btnDisabled =
			!$this->permissionManager->userCan( 'edit', $user, $title );
		if ( $title && $title->canExist() ) {
			$content = $this->wikiPageFactory->newFromTitle( $title )->getContent();
			$this->btnDisabled = $this->btnDisabled || !( $content instanceof WikitextContent );
		}

		parent::__construct(
			'bs-category-inline-editor',
			$this->getCategoryEditorHtml()
		);
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ): bool {
		$title = $context->getTitle();
		if ( !$title || $title->isSpecialPage() ) {
			return false;
		}
		$wikipage = $this->wikiPageFactory->newFromTitle( $title );
		return $wikipage->getContentHandler()->supportsCategories();
	}

	/**
	 *
	 * @return string
	 */
	private function getCategoryEditorHtml(): string {
		/** @var RequestContext */
		$context = RequestContext::getMain();

		/** @var Title */
		$title = $context->getTitle();
		if ( !$title->exists() ) {
			return '';
		}
		$html = '<div class="bs-insert-category-tool">';
		$html .= $this->makeIconButton( $title, $context );
		$html .= $this->makeList( $title, $context );
		$html .= $this->makeEditLink( $context );
		$html .= '</div>';
		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @param Context $context
	 * @return string
	 */
	private function makeIconButton( $title, $context ): string {
		$html = Html::element(
			'button',
			[
				'id' => 'bs-insert-category',
				'class' => 'icon-category-tag-outline',
				'title' => Message::newFromKey( 'bs-insertcategory-edit-dialog-button-tooltip' )->text(),
				'aria-label' => Message::newFromKey( 'bs-insertcategory-edit-dialog-button-aria-label' )->text(),
				'role' => 'button',
				'disabled' => $this->btnDisabled ? 'disabled' : null
			]
		);
		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @param Context $context
	 * @return string
	 */
	private function makeList( $title, RequestContext $context ): string {
		$categoryNames = $this->getCategoriesFromPreference( $context );
		sort( $categoryNames, SORT_NATURAL );

		if ( empty( $categoryNames ) ) {
			$html = Html::element(
				'span',
				[
					'class' => 'bs-category-label'
				],
				Message::newFromKey( 'bs-insertcategory-no-categories' )->text()
			);
		} else {
			$html = '<ul>';
			foreach ( $categoryNames as $name ) {
				$title = Title::makeTitle( NS_CATEGORY, $name );
				$classes = [];

				if ( !$title ) {
					continue;
				}
				if ( !$title->exists() ) {
					$classes[] = 'new';
				}
				$categoryLink = Html::element(
					'a',
					[
						'title' => $title->getPrefixedText(),
						'aria-label' => $title->getPrefixedText(),
						'href' => $title->getLocalURL(),
						'role' => 'link',
						'class' => $classes
					],
					$name
				);

				$html .= '<li>' . $categoryLink . '</li> ';
			}
			$html .= '</ul>';
		}
		return $html;
	}

	/**
	 *
	 * @param RequestContext $context
	 * @return array
	 */
	private function getCategoriesFromPreference( $context ): array {
		$user = $context->getUser();
		$showHiddenCategories = MediaWikiServices::getInstance()
		->getUserOptionsLookup()
		->getBoolOption( $user, 'showhiddencats' );

		$categoryRequestType = 'normal';
		if ( $showHiddenCategories ) {
			$categoryRequestType = 'all';
		}

		$categories = $context->getSkin()->getOutput()->getCategories( $categoryRequestType );
		return $categories;
	}

	/**
	 *
	 * @param Context $context
	 * @return string
	 */
	private function makeEditLink( $context ): string {
		$class = '';
		if ( $this->btnDisabled ) {
			$class = 'isDisabled';
		}

		$editLink = Html::element(
			'a',
			[
				'id' => 'bs-category-link-edit',
				'class' => $class,
				'title' => Message::newFromKey( 'bs-insertcategory-page-header-categories-edit-tooltip' )->text(),
				'role' => 'button',
				'aria-label' => Message::newFromKey( 'bs-insertcategory-edit-dialog-button-aria-label' )->text(),
				'href' => ''
			],
			Message::newFromKey( 'bs-insertcategory-edit-dialog-button-label' )->text()
		);
		return $editLink;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [ 'ext.bluespice.insertcategory.discovery.styles' ];
	}
}
