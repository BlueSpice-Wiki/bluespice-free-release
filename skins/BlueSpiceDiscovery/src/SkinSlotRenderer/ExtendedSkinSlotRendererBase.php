<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

use BlueSpice\Discovery\CookieHandler;
use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\AriaAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\DataAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\HtmlIdRegistry;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRegistry;
use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRendererBase;

abstract class ExtendedSkinSlotRendererBase extends SkinSlotRendererBase {

	/**
	 * @var SkinSlotRegistry
	 */
	protected $skinSlotRegistry;

	/**
	 *
	 * @var HtmlIdRegistry
	 */
	protected $htmlIdRegistry = null;

	/**
	 *
	 * @var CookieHandler
	 */
	protected $cookieHandler = null;

	/**
	 *
	 * @var PermissionManager
	 */
	protected $permissionManager = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/** @var DataAttributesBuilder */
	protected $dataAttributesBuilder;

	/** @var AriaAttributesBuilder */
	protected $ariaAttributesBuilder;

	/**
	 *
	 * @param SkinSlotRegistry $skinSlotRegistry
	 * @param ComponentManager $componentManager
	 * @param RendererDataTreeBuilder $rendererDataTreeBuilder
	 * @param RendererDataTreeRenderer $rendererDataTreeRenderer
	 * @param HtmlIdRegistry $htmlIdRegistry
	 * @param CookieHandler $cookieHandler
	 * @param PermissionManager $permissionManager
	 * @param string $slotId
	 * @param IContextSource $context
	 */
	public function __construct( $skinSlotRegistry, $componentManager, $rendererDataTreeBuilder,
	$rendererDataTreeRenderer, $htmlIdRegistry, $cookieHandler, $permissionManager, $slotId, $context ) {
		$this->skinSlotRegistry = $skinSlotRegistry;
		$this->componentManager = $componentManager;
		$this->rendererDataTreeBuilder = $rendererDataTreeBuilder;
		$this->rendererDataTreeRenderer = $rendererDataTreeRenderer;
		$this->htmlIdRegistry = $htmlIdRegistry;
		$this->cookieHandler = $cookieHandler;
		$this->permissionManager = $permissionManager;
		$this->slotId = $slotId;
		$this->context = $context;
		$this->dataAttributesBuilder = new DataAttributesBuilder();
		$this->ariaAttributesBuilder = new AriaAttributesBuilder();
	}

	/**
	 *
	 * @param array $data
	 * @return string
	 */
	public function getHtml( $data = [] ): string {
		$skinSlots = $this->skinSlotRegistry->getSkinSlots();
		$items = $skinSlots[static::REG_KEY];

		if ( empty( $items ) ) {
			return '';
		}

		$this->sortItems( $items );

		$innerHtml = '';
		foreach ( $items as $id => $item ) {
			if ( !is_callable( $item['factory'] ) ) {
				continue;
			}
			$component = call_user_func_array( $item['factory'], [] );
			if ( !( $component instanceof IComponent ) ) {
				continue;
			}

			$componentTree = $this->componentManager->getCustomComponentTree(
				$component,
				$data
			);

			if ( !empty( $componentTree ) ) {
				$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( [
					array_pop( $componentTree )
				] );
				$innerHtml .= $this->buildOpeningItemWrapperHtml( $component->getId() );
				$innerHtml .= $this->rendererDataTreeRenderer->getHtml( $rendererDataTree );
				$innerHtml .= $this->buildClosingItemWrapperHtml();
			}
		}

		$html = '';
		if ( $innerHtml !== '' ) {
			$html .= $this->buildOpeningConainerWrapperHtml();
			$html .= $innerHtml;
			$html .= $this->buildClosingConainerWrapperHtml();
		}

		return $html;
	}

	/**
	 *
	 * @param array &$items
	 * @return void
	 */
	protected function sortItems( &$items ): void {
		ksort( $items );
	}

	/**
	 *
	 * @return string
	 */
	protected function getContainerWrapperTag(): string {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	protected function getContainerWrapperClasses(): array {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function getContainerWrapperId(): string {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	protected function getContainerWrapperDataAttributes(): array {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	protected function getContainerWrapperAriaAttributes(): array {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function getItemWrapperTag(): string {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	protected function getItemWrapperClasses(): array {
		return [];
	}

	/**
	 *
	 * @param string $id
	 * @return string
	 */
	protected function getItemWrapperId( $id ): string {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	protected function getItemWrapperDataAttributes(): array {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	protected function getItemWrapperAriaAttributes(): array {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	protected function buildOpeningConainerWrapperHtml(): string {
		$html = $this->buildOpeningHtml(
			$this->getContainerWrapperTag(),
			$this->getContainerWrapperId(),
			$this->getContainerWrapperClasses(),
			$this->getContainerWrapperAriaAttributes(),
			$this->getContainerWrapperDataAttributes()
		);

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	protected function buildClosingConainerWrapperHtml(): string {
		$html = $this->buildClosingHtml(
			$this->getContainerWrapperTag()
		);

		return $html;
	}

	/**
	 *
	 * @param string $id
	 * @return string
	 */
	protected function buildOpeningItemWrapperHtml( $id ): string {
		$html = $this->buildOpeningHtml(
			$this->getItemWrapperTag(),
			$this->getItemWrapperId( $id ),
			$this->getItemWrapperClasses(),
			$this->getItemWrapperAriaAttributes(),
			$this->getItemWrapperDataAttributes()
		);

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	protected function buildClosingItemWrapperHtml(): string {
		$html = $this->buildClosingHtml(
			$this->getItemWrapperTag()
		);

		return $html;
	}

	/**
	 *
	 * @param string $tag
	 * @param string $id
	 * @param array $classes
	 * @param array $aria
	 * @param array $data
	 * @param string $role
	 * @return string
	 */
	protected function buildOpeningHtml( $tag, $id, $classes, $aria, $data, $role = '' ): string {
		if ( $tag === '' ) {
			return '';
		}

		$htmlId = '';
		if ( $id !== '' ) {
			$htmlId = ' id="' . $id . '"';
		}

		$htmlClass = '';
		if ( !empty( $classes ) ) {
			$htmlClass = ' class="' . implode( ' ', $classes ) . '"';
		}

		$ariaString = '';
		if ( !empty( $aria ) ) {
			$ariaString = ' ' . $this->ariaAttributesBuilder->toString( $aria );
		}

		$dataString = '';
		if ( !empty( $data ) ) {
			$dataString = ' ' . $this->dataAttributesBuilder->toString( $data );
		}

		$roleString = '';
		$ariaLabelString = '';
		if ( $role ) {
			$roleString = ' role="' . $role . '"';

			if ( !$ariaString ) {
				/*
				* bs-discovery-tools-after-content-cnt-aria-label,
				* bs-discovery-data-after-content-cnt-aria-label
				*/
				$ariaLabel = Message::newFromKey( "bs-discovery-$id-cnt-aria-label" );
				$ariaLabelString = ' aria-label="' . $ariaLabel->escaped() . '"';
			}
		}

		return '<' . $tag . $htmlId . $htmlClass . $ariaString . $dataString . $roleString . $ariaLabelString . '>';
	}

	/**
	 *
	 * @param string $tag
	 * @return string
	 */
	protected function buildClosingHtml( $tag ): string {
		if ( $tag === '' ) {
			return '';
		}

		return '</' . $tag . '>';
	}
}
