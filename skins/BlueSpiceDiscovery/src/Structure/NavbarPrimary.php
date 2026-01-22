<?php

namespace BlueSpice\Discovery\Structure;

use BaseTemplate;
use BlueSpice\Discovery\Component\CreateContentSplitButton;
use BlueSpice\Discovery\Component\GlobalActionsButton;
use BlueSpice\Discovery\Component\SidebarPrimaryToggleButton;
use BlueSpice\Discovery\Component\SidebarPrimaryToggleButtonMobile;
use BlueSpice\Discovery\Component\SidebarSecondaryToggleButton;
use BlueSpice\Discovery\Component\UserButtonLogin;
use BlueSpice\Discovery\Component\UserButtonMenu;
use BlueSpice\Discovery\ISkinLayout;
use BlueSpice\Discovery\ISkinLayoutAware;
use BlueSpice\Discovery\SkinSlotRenderer\NavbarPrimaryCenterItemsSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\NavbarPrimaryItemsSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\NavbarPrimarySearchFormSkinSlotRenderer;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

class NavbarPrimary extends NavbarBase implements ISkinLayoutAware {

	/**
	 * @var BaseTemplate
	 */
	private $template = null;

	/**
	 * @var IContextSource
	 */
	private $context = null;

	/**
	 * @var ISkinLayout
	 */
	private $layout = null;

	/**
	 * @var array
	 */
	private $skinComponents = [];

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'navbar-primary';
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$specialUserLogin = SpecialPage::getSafeTitleFor( 'Userlogin' );
		$title = $context->getTitle();
		if ( $specialUserLogin->equals( $title ) ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotNavbarPrimarySearchFormHtml(): void {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			NavbarPrimarySearchFormSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['search-form'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotNavbarPrimaryItemsHtml(): void {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			NavbarPrimaryItemsSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['navbar-items'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotNavbarPrimaryCenterItemsHtml(): void {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			NavbarPrimaryCenterItemsSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['navbar-center-items'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchNewContentButtonHtml() {
		$component = new CreateContentSplitButton();
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['new-content-button'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSidebarPrimaryToggleButtonHtml() {
		if ( isset( $this->layout->skinStructureElements['sidebar-primary'] ) ) {
			$sidebar = $this->layout->skinStructureElements['sidebar-primary'];

			if ( $sidebar->shouldRender( $this->context ) ) {
				$component = new SidebarPrimaryToggleButton( $this->cookieHandler );
				$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

				$this->skinComponents['sidebar-primary-toggle'] = $html;
			}
		}
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSidebarPrimaryToggleButtonMobileHtml() {
		if ( isset( $this->layout->skinStructureElements['sidebar-primary'] ) ) {
			$sidebar = $this->layout->skinStructureElements['sidebar-primary'];

			if ( $sidebar->shouldRender( $this->context ) ) {
				$component = new SidebarPrimaryToggleButtonMobile( $this->cookieHandler );
				$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

				$this->skinComponents['sidebar-primary-toggle-mobile'] = $html;
			}
		}
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSidebarSecondaryToggleButtonHtml() {
		if ( isset( $this->layout->skinStructureElements['sidebar-secondary'] ) ) {
			$sidebar = $this->layout->skinStructureElements['sidebar-secondary'];

			if ( $sidebar->shouldRender( $this->context ) ) {
				$component = new SidebarSecondaryToggleButton( $this->cookieHandler );
				$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

				$this->skinComponents['sidebar-secondary-toggle'] = $html;
			}
		}
	}

	/**
	 *
	 * @return void
	 */
	private function fetchGlobalActionsButtonHtml() {
		$component = new GlobalActionsButton();
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['global-actions-button'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchLoginButtonHtml() {
		$component = new UserButtonLogin( $this->context );
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['login-button'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchUserMenuButtonHtml() {
		$component = new UserButtonMenu();
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['user-menu-button'] = $html;
	}

	/**
	 *
	 * @return array
	 */
	public function getParams(): array {
		$mainpage = Title::newMainPage();

		$this->fetchSkinSlotNavbarPrimarySearchFormHtml();
		$this->fetchNewContentButtonHtml();
		$this->fetchSkinSlotNavbarPrimaryItemsHtml();
		$this->fetchSkinSlotNavbarPrimaryCenterItemsHtml();
		$this->fetchGlobalActionsButtonHtml();
		$this->fetchLoginButtonHtml();
		$this->fetchUserMenuButtonHtml();
		$this->fetchSidebarSecondaryToggleButtonHtml();
		$this->fetchSidebarPrimaryToggleButtonHtml();
		$this->fetchSidebarPrimaryToggleButtonMobileHtml();

		return array_merge(
			$this->skinComponents,
			[
				'logo-href' => $mainpage->getLocalURL(),
				'logo-title' => $mainpage->getText(),
				'logo-aria-label' => Message::newFromKey(
					'bs-discovery-logo-return-to-aria-label',
					$mainpage->getText()
				)->text(),
				'logo-src' => $this->getActiveLogoPath(),
				'navbar-menu-aria-label' => Message::newFromKey( 'bs-discovery-navbar-aria-label' ),
			]
		);
	}

	/**
	 * @return string
	 */
	private function getActiveLogoPath(): string {
		/** @var Config */
		$config = $this->context->getConfig();

		$logoPath = $config->get( 'Logo' );
		$logos = $config->get( 'Logos' );
		if ( isset( $logos['1x'] ) ) {
			$logoPath = $logos['1x'];
		}
		return $logoPath;
	}

	/**
	 * @param ISkinLayout $layout
	 * @return void
	 */
	public function setSkinLayout( ISkinLayout $layout ): void {
		$this->layout = $layout;
		$this->context = $layout->context;
		$this->template = $layout->template;
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [ 'skin.discovery.navbar-primary.styles' ];
	}
}
