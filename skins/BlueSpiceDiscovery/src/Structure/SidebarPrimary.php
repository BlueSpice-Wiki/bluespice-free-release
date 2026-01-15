<?php

namespace BlueSpice\Discovery\Structure;

use BaseTemplate;
use BlueSpice\Discovery\CookieHandler;
use BlueSpice\Discovery\IBaseTemplateAware;
use MediaWiki\Context\IContextSource;
use MediaWiki\SpecialPage\SpecialPage;

class SidebarPrimary extends StackedTabPanelContainerBase implements IBaseTemplateAware {

	/**
	 * @var BaseTemplate
	 */
	private $template = null;

	/**
	 * @var string
	 */
	private $registryKey = 'SidebarPrimaryTabPanels';

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'sidebar-primary';
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'sb-pri';
	}

	/**
	 *
	 * @return string
	 */
	public function getTabPanelRegistryKey(): string {
		return $this->registryKey;
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses(): array {
		$cookieHandler = new CookieHandler( $this->template->getSkin()->getRequest() );
		$expanded = $cookieHandler->getCookie( $this->getId() . '-cnt' );
		$classes = [ 'col', 'col-west', 'px-0', 'collapse' ];
		if ( $expanded === 'true' ) {
			array_push( $classes, 'show' );
		}

		return $classes;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ): bool {
		if ( !parent::shouldRender( $context ) ) {
			return false;
		}
		$specialUserLogin = SpecialPage::getSafeTitleFor( 'Userlogin' );
		$title = $context->getTitle();
		if ( $specialUserLogin->equals( $title ) ) {
			return false;
		}

		/*
		 * whitelist check necessary for InviteSignup
		 * permission manager messes up with sessions
		 * so that with submit login on invite link user's session
		 * get changed and cannot use invite link to register
		 */
		$config = $context->getConfig();
		$whitelist = $config->get( 'WhitelistRead' );
		if ( $whitelist && in_array( $title->getPrefixedText(), $whitelist ) ) {
			return true;
		}

		$user = $context->getUser();
		if ( !$user->isAllowed( 'read' ) ) {
			return false;
		}

		return true;
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
		return [ 'skin.discovery.sidebar-primary.styles' ];
	}

	/**
	 * @return array
	 */
	public function getScripts(): array {
		return [ 'skin.discovery.bluespice.sidebar-primary.scripts' ];
	}
}
