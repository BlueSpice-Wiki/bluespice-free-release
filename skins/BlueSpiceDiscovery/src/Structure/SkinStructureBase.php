<?php

namespace BlueSpice\Discovery\Structure;

use BlueSpice\Discovery\IResourceProvider;
use BlueSpice\Discovery\ISkinStructure;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\Discovery\ITemplateProvider;
use BlueSpice\Discovery\Renderer\ComponentRenderer;
use BlueSpice\Discovery\Renderer\SkinSlotRenderer;
use MediaWiki\Context\IContextSource;
use MediaWiki\Permissions\PermissionManager;

abstract class SkinStructureBase implements ISkinStructure, ITemplateProvider, IResourceProvider {

	/**
	 * @var array
	 */
	protected $componentProcessData = [];

	/**
	 * @var ComponentRenderer
	 */
	protected $componentRenderer = null;

	/**
	 * @var SkinSlotRenderer
	 */
	protected $skinSlotRenderer = null;

	/**
	 * @var PermissionManager
	 */
	protected $permissionManager = null;

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param ComponentRenderer $componentRenderer
	 * @param SkinSlotRenderer $skinSlotRenderer
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRenderer $skinSlotRenderer,
		PermissionManager $permissionManager ) {
		$this->componentProcessData = $templateDataProvider->getAll();
		$this->componentRenderer = $componentRenderer;
		$this->skinSlotRenderer = $skinSlotRenderer;
		$this->permissionManager = $permissionManager;
	}

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param ComponentRenderer $componentRenderer
	 * @param SkinSlotRenderer $skinSlotRenderer
	 * @param PermissionManager $permissionManager
	 * @return ISkinStructure
	 */
	public static function factory(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRenderer $skinSlotRenderer,
		PermissionManager $permissionManager ) {
		return new static(
			$templateDataProvider, $componentRenderer, $skinSlotRenderer, $permissionManager
		);
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
		return $this->getName();
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
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		return true;
	}

	/**
	 *
	 * @return array
	 */
	public function getParams(): array {
		return [];
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
