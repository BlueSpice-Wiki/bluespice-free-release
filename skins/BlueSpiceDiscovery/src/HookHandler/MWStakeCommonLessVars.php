<?php

namespace BlueSpice\Discovery\HookHandler;

use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUILessVarsInit;

class MWStakeCommonLessVars implements MWStakeCommonUILessVarsInit {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUILessVarsInit( $lessVars ): void {
		$lessVars->setVar( 'navbar-bg', '#fff' );
		$lessVars->setVar( 'navbar-fg', '#252525' );
		$lessVars->setVar( 'navbar-highlight', '#3e5389' );

		$lessVars->setVar( 'sidebar-bg', '#fff' );
		$lessVars->setVar( 'sidebar-fg', '#252525' );
		$lessVars->setVar( 'sidebar-highlight', '#3e5389' );

		$lessVars->setVar( 'footer-bg', '#d8d8d9' );
		$lessVars->setVar( 'footer-fg', '#454545' );

		$lessVars->setVar( 'content-bg', '#fff' );
		$lessVars->setVar( 'content-fg', '#252525' );
		$lessVars->setVar( 'link-fg', '#3e5389' );
		$lessVars->setVar( 'new-link-fg', '#bd1d1d' );

		$lessVars->setVar( 'font-weight-light', '300' );
		$lessVars->setVar( 'font-weight-regular', '400' );
		$lessVars->setVar( 'font-weight-medium', '500' );
		$lessVars->setVar( 'font-weight-bold', '700' );

		$lessVars->setVar( 'content-width', '1200px' );
		$lessVars->setVar( 'content-font-size', '0.9385rem' );
		$lessVars->setVar( 'content-font-weight', '@font-weight-regular' );
		$lessVars->setVar( 'content-primary-font-family', '"Lato"' );
		$lessVars->setVar( 'content-font-family', '@content-primary-font-family, "sans-serif"' );

		$lessVars->setVar( 'content-h1-fg', '#252525' );
		$lessVars->setVar( 'content-h1-font-size', '2rem' );
		$lessVars->setVar( 'content-h1-font-weight', '500' );
		$lessVars->setVar( 'content-h1-border', 'none' );

		$lessVars->setVar( 'content-h2-fg', '#252525' );
		$lessVars->setVar( 'content-h2-font-size', '1.8rem' );
		$lessVars->setVar( 'content-h2-font-weight', 'bold' );
		$lessVars->setVar( 'content-h2-border', 'none' );

		$lessVars->setVar( 'content-h3-fg', '#252525' );
		$lessVars->setVar( 'content-h3-font-size', '1.6rem' );
		$lessVars->setVar( 'content-h3-font-weight', 'bold' );
		$lessVars->setVar( 'content-h3-border', 'none' );

		$lessVars->setVar( 'content-h4-fg', '#252525' );
		$lessVars->setVar( 'content-h4-font-size', '1.4rem' );
		$lessVars->setVar( 'content-h4-font-weight', 'bold' );
		$lessVars->setVar( 'content-h4-border', 'none' );

		$lessVars->setVar( 'content-h5-fg', '#252525' );
		$lessVars->setVar( 'content-h5-font-size', '1.25rem' );
		$lessVars->setVar( 'content-h5-font-weight', 'bold' );
		$lessVars->setVar( 'content-h5-border', 'none' );

		$lessVars->setVar( 'content-h6-fg', '#252525' );
		$lessVars->setVar( 'content-h6-font-size', '1.1rem' );
		$lessVars->setVar( 'content-h6-font-weight', 'bold' );
		$lessVars->setVar( 'content-h6-border', 'none' );
	}
}
