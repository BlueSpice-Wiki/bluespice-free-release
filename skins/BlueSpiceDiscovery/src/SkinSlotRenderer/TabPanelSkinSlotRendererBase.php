<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

use MediaWiki\Html\TemplateParser;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITabPanel;

abstract class TabPanelSkinSlotRendererBase extends ExtendedSkinSlotRendererBase {

	/**
	 * @var array
	 */
	private $params = [];

	/**
	 *
	 * @var string
	 */
	private $templatePath = '';

	/**
	 *
	 * @var string
	 */
	private $templateName = '';

	/**
	 *
	 * @param array $data
	 * @return string
	 */
	public function getHtml( $data = [] ): string {
		$skinSlots = $this->skinSlotRegistry->getSkinSlots();
		$items = $skinSlots[static::REG_KEY];

		$tabPanels = [];
		foreach ( $items as $key => $item ) {
			if ( !is_callable( $item['factory'] ) ) {
				continue;
			}
			$component = call_user_func_array( $item['factory'], [] );
			if ( $component instanceof IComponent ) {
				$tabPanels[] = $component;
			}
		}

		$this->findActiveId( $tabPanels );

		$this->getTemplate();
		$templateParser = new TemplateParser(
			$this->templatePath
		);
		$templateParser->enableRecursivePartials( false );
		$html = $templateParser->processTemplate(
			$this->templateName,
			$this->buildParams( $tabPanels )
		);

		return $html;
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
			}
		}
	}

	/**
	 *
	 * @param array $tabPanels
	 * @return array
	 */
	private function buildParams( $tabPanels ): array {
		$first = true;
		$mainPanel = [];
		$otherPanels = [];
		foreach ( $tabPanels as $id => $tabPanel ) {
			if ( !( $tabPanel instanceof ITabPanel ) ) {
				continue;
			}

			$subComponents = $tabPanel->getSubComponents();

			$body = '';
			foreach ( $subComponents as $subComponent ) {
				if ( !empty( $subComponent ) ) {
					$body .= $this->getComponentHtml( $subComponent );
				}
			}

			$class = '';
			if ( $tabPanel->getId() === $this->activeId ) {
				$class = 'show active';
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
		if ( array_key_exists( 'panel', $mainPanel ) ) {
			foreach ( $mainPanel['panel'] as $panel ) {
				if ( array_key_exists( 'list', $otherPanels ) ) {
					$panel = array_merge( $panel, [ 'list' => $otherPanels['list'] ] );
				}
			}

			$main[] = $panel;
		}

		$other = [];
		if ( array_key_exists( 'panel', $otherPanels ) ) {
			foreach ( $otherPanels['panel'] as $panel ) {
				if ( array_key_exists( 'list', $mainPanel ) ) {
					$panel = array_merge( $panel, [ 'list' => $mainPanel['list'] ] );
				}

				$other[] = $panel;
			}
		}

		return [
			'main' => $main,
			'other' => $other
		];
	}

	/**
	 *
	 * @param IComponent $component
	 * @return string
	 */
	private function getComponentHtml( $component ): string {
		$componentTree = $this->componentManager->getCustomComponentTree(
			$component,
			$this->componentProcessData
		);

		if ( empty( $componentTree ) ) {
			return '';
		}

		$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( [ array_pop( $componentTree ) ] );
		return $this->rendererDataTreeRenderer->getHtml( $rendererDataTree );
	}

	/**
	 * @return string
	 */
	public function getTemplatePath(): string {
		return $GLOBALS['wgStyleDirectory'] .
			'/BlueSpiceDiscovery/resources/templates/renderer/stacked-tab-panel-container';
	}

	/**
	 * @inheritDoc
	 */
	private function getTemplate() {
		$templatePath = $this->getTemplatePath();
		$templateParts = explode( '/', $templatePath );
		$this->templateName = array_pop( $templateParts );
		$this->templatePath = implode( '/', $templateParts );
	}
}
