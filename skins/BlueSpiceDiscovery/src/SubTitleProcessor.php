<?php

namespace BlueSpice\Discovery;

class SubTitleProcessor {

	/**
	 *
	 * @var string
	 */
	private $subtitle = "";

	/**
	 *
	 * @var string
	 */
	private $subpages = "";

	/**
	 *
	 * @var string
	 */
	private $backlink = "";

	/**
	 *
	 * @var string
	 */
	private $redirect = "";

	/**
	 *
	 * @var string
	 */
	private $revision = "";

	public function __construct() {
	}

	/**
	 * @param string $subtitle
	 */
	public function parse( $subtitle ) {
		$this->subtitle = $subtitle;
		$this->checkRedirect();
		$this->checkSubpages();
		$this->checkBacklink();
		$this->checkRevision();
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function get( $key = '' ) {
		if ( $key === 'redirect' ) {
			return $this->redirect;
		}
		if ( $key === 'backlink' ) {
			return $this->backlink;
		}
		if ( $key === 'subpages' ) {
			return $this->subpages;
		}
		if ( $key === 'revision' ) {
			return $this->revision;
		}
		if ( $this->subtitle === '/v' ||
			$this->subtitle === '<br>' ) {
			$this->subtitle = '';
		}
		return $this->subtitle;
	}

	/**
	 *
	 * @return void
	 */
	private function checkRedirect() {
		$hasRedirect = preg_match(
			'#(<span class="mw-redirectedfrom">)(.*?)(<\/span>)#',
			$this->subtitle,
			$matches
		);
		if ( $hasRedirect ) {
			$this->redirect = $matches[0];
			$this->subtitle = preg_replace(
			'#(<span class="mw-redirectedfrom">)(.*?)(<\/span>)#',
			'',
			$this->subtitle );
		}
		$hasRedirectSub = preg_match(
			'#(<span id="redirectsub">)(.*?)(<\/span>)#',
			$this->subtitle,
			$matchesSub
		);
		if ( $hasRedirectSub ) {
			$redirectSub = $matchesSub[0];
			$this->subtitle = preg_replace(
				'#(<span id="redirectsub">)(.*?)(<\/span>)#',
				'',
				$this->subtitle );
		}
	}

	/**
	 *
	 * @return void
	 */
	private function checkSubpages() {
		$hasSubpages = preg_match(
			'#(<div class="subpages">)(.*?)(<\/div>)#',
			$this->subtitle,
			$matches
		);
		if ( $hasSubpages ) {
			$this->subpages = $matches[0];
			$this->subtitle = preg_replace(
			'#(<div class="subpages">)(.*?)(<\/div>)#',
			'',
			$this->subtitle );
		}
	}

	/**
	 *
	 * @return void
	 */
	private function checkBacklink() {
		$hasBacklink = preg_match(
			'#(← <a .*?>)(.*?)(<\/a>)#',
			$this->subtitle,
			$matches
		);
		if ( $hasBacklink ) {
			$this->backlink = $matches[0];
			$this->subtitle = preg_replace(
			'#(← <a .*?>)(.*?)(<\/a>)#',
			'',
			$this->subtitle );
		}
	}

	/**
	 *
	 * @return void
	 */
	private function checkRevision() {
		$hasRevision = preg_match(
			'#(<div class="mw-revision.*?>)(.*?)(<\/div>)#',
			$this->subtitle,
			$matches
		);

		if ( $hasRevision ) {
			$this->revision = $matches[0];
			$this->subtitle = preg_replace(
			'#(<div id="mw-revision-nav.*?>)(.*?)(<\/div>)#',
			'',
			$this->subtitle );
			$this->subtitle = preg_replace(
			'#(<div id="mw-revision-info.*?>)(.*?)(<\/div>)#',
			'',
			$this->subtitle );
			$this->subtitle = preg_replace(
			'#(<div class="mw-revision.*?>)(.*?)(<\/div>)#',
			'',
			$this->subtitle );
		}
	}
}
