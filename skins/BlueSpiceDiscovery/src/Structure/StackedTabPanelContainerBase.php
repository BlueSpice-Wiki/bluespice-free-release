<?php

namespace BlueSpice\Discovery\Structure;

use BlueSpice\Discovery\IContextSourceAware;
use BlueSpice\Discovery\IResourceProvider;
use BlueSpice\Discovery\ISkinStructure;
use BlueSpice\Discovery\ITabPanelContainer;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\Discovery\ITemplateProvider;
use BlueSpice\Discovery\Renderer\ComponentRenderer;
use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITabPanel;
use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

abstract class StackedTabPanelContainerBase
	implements ISkinStructure, ITabPanelContainer, ITemplateProvider, IContextSourceAware, IResourceProvider {

	/**
	 * @var array
	 */
	private $skinComponents = [];

	/**
	 * @var string
	 */
	private $activeId = '';

	/**
	 * @var IContextSource
	 */
	private $context = null;

	/**
	 * @var array
	 */
	protected $componentProcessData = [];

	/**
	 * @var ComponentRenderer
	 */
	protected $componentRenderer = null;

	/**
	 * @var SkinSlotRegistry
	 */
	protected $skinSlotRegistry = null;

	/**
	 * @var ObjectFactory
	 */
	protected $objectFactory = null;

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param ComponentRenderer $componentRenderer
	 * @param SkinSlotRegistry $skinSlotRegistry
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRegistry $skinSlotRegistry,
		ObjectFactory $objectFactory
		) {
		$this->componentProcessData = $templateDataProvider->getAll();
		$this->componentRenderer = $componentRenderer;
		$this->skinSlotRegistry = $skinSlotRegistry;
		$this->objectFactory = $objectFactory;
	}

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param ComponentRenderer $componentRenderer
	 * @param SkinSlotRegistry $skinSlotRegistry
	 * @param ObjectFactory $objectFactory
	 * @return ISkinStructure
	 */
	public static function factory(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRegistry $skinSlotRegistry,
		ObjectFactory $objectFactory
		) {
		return new static(
			$templateDataProvider, $componentRenderer, $skinSlotRegistry, $objectFactory
		);
	}

	/**
	 *
	 * @return array
	 */
	private function getTabPanels(): array {
		$skinSlots = $this->skinSlotRegistry->getSkinSlots();
		$skinSlot = $skinSlots[$this->getTabPanelRegistryKey()];
		$tabPanels = [];
		foreach ( $skinSlot as $key => $item ) {
			if ( isset( $item['factory'] ) && is_array( $item['factory'] ) ) {
				$callback = end( $item['factory'] );
				$item['factory'] = $callback;
			}
			if ( isset( $item['class'] ) && is_array( $item['class'] ) ) {
				$callback = end( $item['class'] );
				$item['class'] = $callback;
			}
			if ( isset( $item['factory'] ) && isset( $item['class'] ) ) {
				unset( $item['factory'] );
			}

			$component = $this->objectFactory->createObject( $item );
			if ( ( $component instanceof IComponent ) ) {
				$tabPanels[$key] = $component;
			}
		}
		krsort( $tabPanels );

		return $tabPanels;
	}

	/**
	 * Params for tab panels
	 *  '0' => [
	 *          'body' => 'value1',
	 *          ],
	 *  '1' => ...
	 */
	private function buildTabPanels() {
		$tabPanels = $this->getTabPanels();

		$this->findActiveId( $tabPanels );

		$first = true;
		$mainPanel = [];
		$otherPanels = [];
		foreach ( $tabPanels as $key => $tabPanel ) {

			if ( !( $tabPanel instanceof ITabPanel )
			 || !$tabPanel->shouldRender( $this->context ) ) {
				continue;
			}

			$body = '';
			$subComponents = $tabPanel->getSubComponents();
			foreach ( $subComponents as $subComponent ) {
				if ( !empty( $subComponent ) ) {
					$body .= $this->componentRenderer->getComponentHtml(
						$subComponent,
						$this->componentProcessData
					);
				}
			}
			if ( $body === '' ) {
				continue;
			}

			$class = '';
			if ( $tabPanel->getId() === $this->activeId ) {
				$class = ' show active';
			}

			if ( $first ) {
				$first = false;
				$mainPanel['list'][] = [
					'id' => $tabPanel->getId(),
					'title' => $tabPanel->getTitle()->text(),
					'text' => $tabPanel->getText()->text(),
					'aria-label' => $tabPanel->getAriaLabel()->text()
				];
				$mainPanel['panel'][] = [
					'body' => $body,
					'id' => $tabPanel->getId(),
					'desc' => $tabPanel->getAriaDesc()->text(),
					'class' => $class
				];
			} else {
				$otherPanels['list'][] = [
					'id' => $tabPanel->getId(),
					'title' => $tabPanel->getTitle()->text(),
					'text' => $tabPanel->getText()->text(),
					'aria-label' => $tabPanel->getAriaLabel()->text()
				];
				$otherPanels['panel'][] = [
					'body' => $body,
					'id' => $tabPanel->getId(),
					'desc' => $tabPanel->getAriaDesc()->text(),
					'class' => $class
				];
			}
		}

		$main = [];
		if ( isset( $mainPanel['panel'] ) ) {
			foreach ( $mainPanel['panel'] as $panel ) {
				if ( isset( $otherPanels['list'] ) ) {
					$panel = array_merge( $panel, [ 'list' => $otherPanels['list'] ] );
				}
			}

			$main[] = $panel;
		}

		$other = [];
		if ( isset( $otherPanels['panel'] ) ) {
			foreach ( $otherPanels['panel'] as $panel ) {
				if ( isset( $mainPanel['list'] ) ) {
					$panel = array_merge( $panel, [ 'list' => $mainPanel['list'] ] );
				}

				$other[] = $panel;
			}
		}

		if ( !empty( $main ) ) {
			$this->skinComponents['sidebar']['main'] = $main;
		}

		if ( !empty( $other ) ) {
			$this->skinComponents['sidebar']['other'] = $other;
		}
	}

	/**
	 *
	 * @param ITabPanel[] $tabPanels
	 * @return void
	 */
	private function findActiveId( $tabPanels ): void {
		$first = true;
		foreach ( $tabPanels as $id => $tabPanel ) {
			if ( !( $tabPanel instanceof ITabPanel ) ) {
				continue;
			}
			if ( $first ) {
				$first = false;
				$this->activeId = $tabPanel->getId();
			}
			if ( $tabPanel->isActive( $this->context ) ) {
				$this->activeId = $tabPanel->getId();
				break;
			}
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getParams(): array {
		$this->buildTabPanels();
		// bs-discovery-sidebar-primary-cnt-aria-label
		// bs-discovery-sidebar-secondary-cnt-aria-label
		$params = [
			'id' => $this->getId(),
			'aria-label' => Message::newFromKey( 'bs-discovery-' . $this->getName() . '-cnt-aria-label' )->text()
		];
		if ( !empty( $this->getClasses() ) ) {
			$params = array_merge(
				$params,
				$this->skinComponents,
				[
					'container-class' => implode( ' ', $this->getClasses() )
				]
			);
		}

		return $params;
	}

	/**
	 * @return string
	 */
	public function getTemplatePath(): string {
		return $GLOBALS['wgStyleDirectory'] .
			'/BlueSpiceDiscovery/resources/templates/structure';
	}

	/**
	 * @return string
	 */
	public function getTemplateName(): string {
		return 'stacked-tab-panel-container';
	}

	/**
	 * Parse templates recursive
	 *
	 * @return bool
	 */
	public function enableRecursivePartials(): bool {
		return false;
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses(): array {
		return [];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$shouldRender = false;
		$tabPanels = $this->getTabPanels();
		foreach ( $tabPanels as $id => $tabPanel ) {
			if ( !( $tabPanel instanceof ITabPanel ) ) {
				continue;
			}
			$subComponents = $tabPanel->getSubComponents();
			foreach ( $subComponents as $subComponent ) {
				if ( $subComponent->shouldRender( $context ) ) {
					$shouldRender = true;
				}
			}
		}
		return $shouldRender;
	}

	/**
	 * @param IContextSource $context
	 * @return void
	 */
	public function setContextSource( IContextSource $context ): void {
		$this->context = $context;
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [];
	}

	/**
	 * @return array
	 */
	public function getScripts(): array {
		return [];
	}
}
