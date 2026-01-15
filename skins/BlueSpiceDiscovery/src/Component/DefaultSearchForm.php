<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\Renderer\DefaultSearchFormRenderer;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;

class DefaultSearchForm extends Literal {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct(
			'a-default-search-form',
			''
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml(): string {
		$renderer = new DefaultSearchFormRenderer( $this->getParams() );
		return $renderer->getHtml();
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 *
	 * @return array
	 */
	private function getParams(): array {
		$specialSearch = SpecialPage::getTitleFor( 'Search' );

		$params = [
			'form-id' => 'searchform',
			'form-class' => 'form-inline input-group',
			'form-action' => $specialSearch->getFullURL(),
			'button-id' => 'mw-searchButton',
			'button-class' => 'input-group-text bi bi-search',
			'button-title' => Message::newFromKey( 'bs-discovery-searchform-button-title' )->text(),
			'button-aria-label' => Message::newFromKey( 'bs-discovery-searchform-button-aria-label' )->text(),
			'button-name' => 'fulltext',
			'input-id' => 'searchInput',
			'input-class' => 'form-control input_pass',
			'input-placeholder' => Message::newFromKey( 'bs-discovery-searchform-input-placeholder' )->text(),
			'input-aria-label' => Message::newFromKey( 'bs-discovery-searchform-input-aria-label' )->text(),
			'input-autocomplete' => 'off',
			'input-maxlength' => '50'
		];
		return $params;
	}
}
