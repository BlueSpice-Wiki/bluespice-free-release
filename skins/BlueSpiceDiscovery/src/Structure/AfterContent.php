<?php

namespace BlueSpice\Discovery\Structure;

use BaseTemplate;
use BlueSpice\Discovery\IBaseTemplateAware;
use BlueSpice\Discovery\SkinSlotRenderer\DataAfterContentSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\ToolsAfterContentSkinSlotRenderer;

class AfterContent extends SkinStructureBase implements IBaseTemplateAware {

	/** @var array */
	protected $skinComponents = [];

	/**
	 * @var BaseTemplate
	 */
	private $template = null;

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'aftercontent';
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		$this->skinComponents['inner'] = [];

		$this->fetchDataAfterContent();
		$this->fetchSkinSlotToolsAfterContent();
		$this->fetchSkinSlotDataAfterContent();

		return $this->skinComponents;
	}

	/**
	 * Give MediaWiki extensions a chance to add data after content
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#dataAfterContent
	 *
	 * @return void
	 */
	private function fetchDataAfterContent() {
		$html = $this->template->get( 'dataAfterContent' );
		if ( $html !== '' ) {
			$this->skinComponents['inner']['data-after-content'] = $html;
		}
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotDataAfterContent() {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			DataAfterContentSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		if ( $html !== '' ) {
			$this->skinComponents['inner']['skin-slot-data-after-content'] = $html;
		}
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotToolsAfterContent() {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			ToolsAfterContentSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		if ( $html !== '' ) {
			$this->skinComponents['inner']['skin-slot-tools-after-content'] = $html;
		}
	}

	/**
	 * @param BaseTemplate $baseTemplate
	 * @return void
	 */
	public function setBaseTemplate( BaseTemplate $baseTemplate ): void {
		$this->template = $baseTemplate;
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [ 'skin.discovery.aftercontent.styles' ];
	}
}
