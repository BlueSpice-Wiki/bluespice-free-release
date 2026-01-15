<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

class LessVars {

	/** @var LessVars */
	protected static $instance = null;

	/** @var array */
	protected $vars = [];

	/**
	 * @return static
	 */
	public static function getInstance() {
		if ( static::$instance === null ) {
			$lessVars = new static();

			$lessVars->setVar( 'primary-bg', '' );
			$lessVars->setVar( 'secondary-bg', '' );
			$lessVars->setVar( 'tertiary-bg', '' );
			$lessVars->setVar( 'quaternary-bg', '' );
			$lessVars->setVar( 'primary-fg', '' );
			$lessVars->setVar( 'secondary-fg', '' );
			$lessVars->setVar( 'tertiary-fg', '' );
			$lessVars->setVar( 'quaternary-fg', '' );
			$lessVars->setVar( 'body-bg', '' );

			$lessVars->setVar( 'navbar-bg', '' );
			$lessVars->setVar( 'navbar-fg', '' );
			$lessVars->setVar( 'navbar-highlight', '' );

			$lessVars->setVar( 'sidebar-bg', '' );
			$lessVars->setVar( 'sidebar-fg', '' );
			$lessVars->setVar( 'sidebar-highlight', '' );

			$lessVars->setVar( 'footer-bg', '' );
			$lessVars->setVar( 'footer-fg', '' );

			$lessVars->setVar( 'content-bg', '' );
			$lessVars->setVar( 'content-fg', '' );
			$lessVars->setVar( 'content-width', '' );
			$lessVars->setVar( 'link-fg', '' );
			$lessVars->setVar( 'new-link-fg', '' );
			$lessVars->setVar( 'content-font-size', '' );
			$lessVars->setVar( 'content-font-weight', '' );
			$lessVars->setVar( 'content-primary-font-family', '' );

			$lessVars->setVar( 'content-font-family', '' );

			$lessVars->setVar( 'content-h1-fg', '' );
			$lessVars->setVar( 'content-h1-font-size', '' );
			$lessVars->setVar( 'content-h1-font-weight', '' );
			$lessVars->setVar( 'content-h1-border', '' );

			$lessVars->setVar( 'content-h2-fg', '' );
			$lessVars->setVar( 'content-h2-font-size', '' );
			$lessVars->setVar( 'content-h2-font-weight', '' );
			$lessVars->setVar( 'content-h2-border', '' );

			$lessVars->setVar( 'content-h3-fg', '' );
			$lessVars->setVar( 'content-h3-font-size', '' );
			$lessVars->setVar( 'content-h3-font-weight', '' );
			$lessVars->setVar( 'content-h3-border', '' );

			$lessVars->setVar( 'content-h4-fg', '' );
			$lessVars->setVar( 'content-h4-font-size', '' );
			$lessVars->setVar( 'content-h4-font-weight', '' );
			$lessVars->setVar( 'content-h4-border', '' );

			$lessVars->setVar( 'content-h5-fg', '' );
			$lessVars->setVar( 'content-h5-font-size', '' );
			$lessVars->setVar( 'content-h5-font-weight', '' );
			$lessVars->setVar( 'content-h5-border', '' );

			$lessVars->setVar( 'content-h6-fg', '' );
			$lessVars->setVar( 'content-h6-font-size', '' );
			$lessVars->setVar( 'content-h6-font-weight', '' );
			$lessVars->setVar( 'content-h6-border', '' );

			static::$instance = $lessVars;
		}
		\MediaWiki\MediaWikiServices::getInstance()->getHookContainer()->run(
			'MWStakeCommonUILessVarsInit', [ static::$instance ]
		);
		\MediaWiki\MediaWikiServices::getInstance()->getHookContainer()->run(
			'MWStakeCommonUILessVarsOverride', [ static::$instance ]
		);

		return static::$instance;
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 * @return string
	 */
	public function getVar( $name, $default = null ) {
		if ( isset( $this->vars[$name] ) ) {
			return $this->vars[$name];
		}

		return $default;
	}

	/**
	 * @return array
	 */
	public function getAllVars() {
		return $this->vars;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function setVar( $name, $value ) {
		$this->vars[$name] = $value;
	}
}
