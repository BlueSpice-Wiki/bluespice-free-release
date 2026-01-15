<?php

namespace BlueSpice\Discovery\Layout;

use BaseTemplate;
use BlueSpice\Discovery\IBaseTemplateAware;
use BlueSpice\Discovery\IContextSourceAware;
use BlueSpice\Discovery\IResourceProvider;
use BlueSpice\Discovery\ISkinLayout;
use BlueSpice\Discovery\ISkinLayoutAware;
use BlueSpice\Discovery\ITemplateProvider;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Registration\ExtensionRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

abstract class SkinLayoutBase implements
	ISkinLayout,
	IBaseTemplateAware,
	IContextSourceAware,
	IResourceProvider,
	ITemplateProvider
{

	/**
	 *
	 * @var RequestContext
	 */
	public $context;

	/**
	 *
	 * @var BaseTemplate
	 */
	public $template;

	/**
	 *
	 * @var array
	 */
	public $skinStructureElements = [];

	/**
	 *
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 *
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct( ObjectFactory $objectFactory ) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 *
	 * @param ObjectFactory $objectFactory
	 * @return ISkinLayout
	 */
	public static function factory( ObjectFactory $objectFactory ): ISkinLayout {
		return new static( $objectFactory );
	}

	/**
	 *
	 * @return array
	 */
	private function findUsedStructureElements(): array {
		$structureElements = [];
		$layoutName = $this->getName();
		$structureRegistry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoveryStructureRegistry'
		);

		$usedComponents = $this->getStructureElementNames();

		foreach ( $structureRegistry as $name => $structureSpec ) {
			if ( !in_array( $name, $usedComponents ) ) {
				continue;
			}
			if ( isset( $structureSpec['factory'] ) && is_array( $structureSpec['factory'] ) ) {
				$callback = end( $structureSpec['factory'] );
				$structureSpec['factory'] = $callback;
			}
			if ( isset( $structureSpec['class'] ) && is_array( $structureSpec['class'] ) ) {
				$callback = end( $structureSpec['class'] );
				$structureSpec['class'] = $callback;
			}
			if ( isset( $structureSpec['factory'] ) && isset( $structureSpec['class'] ) ) {
				unset( $structureSpec['factory'] );
			}

			$structureElement = $this->objectFactory->createObject( $structureSpec );

			if ( ( $structureElement instanceof IBaseTemplateAware )
				&& ( $this->template instanceof BaseTemplate ) ) {
				$structureElement->setBaseTemplate( $this->template );
			}

			if ( ( $structureElement instanceof IContextSourceAware )
				&& ( $this->context instanceof IContextSource ) ) {
				$structureElement->setContextSource( $this->context );
			}

			if ( $structureElement instanceof ISkinLayoutAware ) {
				$structureElement->setSkinLayout( $this );
			}

			$structureElements[$name] = $structureElement;
		}

		return $structureElements;
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
	public function getSkinStructureElements(): array {
		$this->skinStructureElements = $this->findUsedStructureElements();
		return $this->skinStructureElements;
	}

	/**
	 * @param BaseTemplate $baseTemplate
	 * @return void
	 */
	public function setBaseTemplate( BaseTemplate $baseTemplate ): void {
		$this->template = $baseTemplate;
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
		return [
			'skin.discovery.bluespice.styles',
			'skin.discovery.bluespice.themes.default'
		];
	}

	/**
	 * @return array
	 */
	public function getScripts(): array {
		return [ 'skin.discovery.bluespice.scripts' ];
	}

	/**
	 * @return string
	 */
	public function getTemplateName(): string {
		return $this->getName();
	}

	/**
	 * @return string
	 */
	public function getTemplatePath(): string {
		return 'skins/BlueSpiceDiscovery/resources/templates/layout';
	}
}
