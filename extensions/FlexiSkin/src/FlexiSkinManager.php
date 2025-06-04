<?php

namespace MediaWiki\Extension\FlexiSkin;

use MediaWiki\Context\RequestContext;
use MediaWiki\Json\FormatJson;
use MediaWiki\Registration\ExtensionRegistry;
use MWException;

class FlexiSkinManager implements IFlexiSkinManager {
	/** @var string */
	private $fsPath = '';
	/** @var IFlexiSkin|null */
	private $currentSkin = null;
	/** @var IFlexiSkin[] */
	private $loadedSkins = [];

	/**
	 * @param string $path
	 */
	public function __construct( $path ) {
		$this->fsPath = $path;
	}

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return int
	 */
	public function save( IFlexiSkin $flexiSkin ) {
		$fullPath = $this->getFullPath( $flexiSkin->getName() );

		if ( $flexiSkin->getId() === null ) {
			$flexiSkin = new FlexiSkin(
				1,
				$flexiSkin->getName(),
				$flexiSkin->getConfig(),
				$flexiSkin->isActive()
			);
		}

		$json = FormatJson::encode( $flexiSkin );
		$res = (bool)file_put_contents( $fullPath, $json );
		if ( $res ) {
			$this->currentSkin = $flexiSkin;
		}

		return $res;
	}

	/**
	 * @return int
	 */
	public function delete() {
		$fullPath = $this->getFullPath();
		$this->currentSkin = null;
		if ( file_exists( $fullPath ) ) {
			return unlink( $fullPath );
		}

		return true;
	}

	/**
	 * @param string $skinname
	 * @return IFlexiSkin|null
	 */
	public function getFlexiSkin( $skinname = '' ): ?IFlexiSkin {
		$this->load( false, $skinname );
		return $this->currentSkin;
	}

	/**
	 * @param bool|null $reload
	 * @param string $skinname
	 * @throws MWException
	 * @return void
	 */
	private function load( $reload = false, $skinname = '' ) {
		if ( !empty( $skinname ) ) {
				$this->currentSkin = isset( $this->loadedSkins[$skinname] )
					? $this->loadedSkins[$skinname] : null;
		}
		if ( $this->currentSkin === null || $reload ) {
			$fullPath = $this->getFullPath( $skinname );
			if ( !file_exists( $fullPath ) ) {
				return;
			}
			$json = file_get_contents( $fullPath );
			$newSkin = FlexiSkin::newFromData( FormatJson::decode( $json, 1 ) );
			$skinname = $newSkin->getName();
			$this->loadedSkins[$skinname] = $newSkin;
			$this->currentSkin = $this->loadedSkins[$skinname];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getActive( $skinname = '' ): ?IFlexiSkin {
		if ( empty( $skinname ) ) {
			$skinname = $this->getCurrentSkinname();
		}
		$current = $this->getFlexiSkin( $skinname );
		if ( $current && $current->isActive() ) {
			return $current;
		}
		return null;
	}

	/**
	 * @return string
	 */
	private function getCurrentSkinname() {
		$context = RequestContext::getMain();
		if ( defined( 'MW_NO_SESSION' ) && MW_NO_SESSION ) {
			return $context->getRequest()->getVal( 'skin', '' );
		}
		$skinname = $context->getSkin()->getSkinName();
		return $skinname;
	}

	/**
	 * @inheritDoc
	 */
	public function setActive( ?IFlexiSkin $flexiSkin = null, $active = true ): bool {
		$current = $flexiSkin;
		if ( $current === null ) {
			$context = RequestContext::getMain();
			$currentSkin = $context->getSkin()->getSkinName();
			$current = $this->create( $currentSkin, [] );
		}
		$newSkin = new FlexiSkin(
			$current->getId(),
			$current->getName(),
			$current->getConfig(),
			$active
		);

		return (bool)$this->save( $newSkin );
	}

	/**
	 * @param string $name
	 * @param array $config
	 * @return IFlexiSkin
	 */
	public function create( $name, $config ): IFlexiSkin {
		$existingSkin = $this->getFlexiSkin( $name );
		if ( $existingSkin === null ) {
			return new FlexiSkin( null, $name, $config );
		}

		return $existingSkin;
	}

	/**
	 * @return IPlugin[]
	 */
	public function getPlugins(): array {
		return $this->getRegistryInstances( 'FlexiSkinPluginRegistry', IPlugin::class );
	}

	/**
	 * @return IFlexiSkinSubscriber[]
	 */
	public function getSubscribers() {
		return $this->getRegistryInstances(
			'FlexiSkinSubscriberRegistry',
			IFlexiSkinSubscriber::class
		);
	}

	/**
	 *
	 * @param string $skinname
	 * @return array
	 */
	public function getActiveConfig( $skinname = '' ): array {
		if ( empty( $skinname ) ) {
			$skinname = $this->getCurrentSkinname();
		}
		$active = $this->getActive( $skinname );
		if ( !$active instanceof IFlexiSkin ) {
			return [];
		}

		$config = $active->getConfig();
		if ( !$config ) {
			return [];
		}
		/**
		 * @var string $pluginKey
		 * @var IPlugin $plugin
		 */
		foreach ( $this->getPlugins() as $pluginKey => $plugin ) {
			$plugin->adaptConfiguration( $config );
		}
		return $config;
	}

	/**
	 *
	 * @param string $skinname
	 * @return array
	 */
	public function getActiveLessVars( $skinname = '' ): array {
		if ( empty( $skinname ) ) {
			$skinname = $this->getCurrentSkinname();
		}
		$active = $this->getActive( $skinname );
		if ( !$active instanceof IFlexiSkin ) {
			return [];
		}
		$vars = [];
		foreach ( $this->getPlugins() as $pluginKey => $plugin ) {
			$vars = array_merge( $vars, $plugin->getLessVars( $active ) );
		}
		return $vars;
	}

	/**
	 * Make sure filesystem path is available
	 */
	private function assertPath() {
		if ( !file_exists( $this->fsPath ) ) {
			mkdir( $this->fsPath, 0755, true );
		}
	}

	/**
	 * @param string $skinname
	 * @return string
	 */
	private function getFullPath( $skinname = 'default' ) {
		$this->assertPath();
		return $this->fsPath . $skinname . '.json';
	}

	/**
	 *
	 * @param string $name
	 * @param string $targetClass
	 * @return array
	 */
	public function getRegistryInstances( $name, $targetClass ) {
		$values = ExtensionRegistry::getInstance()->getAttribute( $name );
		$items = [];
		foreach ( $values as $key => $factory ) {
			$item = call_user_func_array( $factory, [] );
			if ( $item instanceof $targetClass ) {
				$items[$key] = $item;
			}
		}

		return $items;
	}
}
