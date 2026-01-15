<?php

use MediaWiki\Html\TemplateParser;

/**
 * Renders the BlueSpice About special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 *
 * @package    BlueSpiceAbout
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

class SpecialBlueSpiceAbout extends \BlueSpice\SpecialPage {

	/**
	 * Constructor of SpecialBlueSpiceAbout class
	 */
	public function __construct() {
		parent::__construct( 'BlueSpiceAbout', 'bluespiceabout-viewspecialpage' );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Not used.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$sLang = $this->getLanguage()->getCode();
		switch ( substr( $sLang, 0, 2 ) ) {
			case "de":
				$sLangKey = "DE";
				break;
			default:
				$sLangKey = "EN";
		}

		$templateParser = new TemplateParser( __DIR__ . '/../templates' );
		global $wgExtensionAssetsPath;

		$oOutputPage = $this->getOutput();
		$oOutputPage->addHTML(
			$templateParser->processTemplate(
				'AboutBlueSpice' . $sLangKey,
				[
					'srcMainPic' => $wgExtensionAssetsPath .
						'/BlueSpiceAbout/resources/images/' .
						'BlueSpice_MediaWiki_Screen_' . $sLangKey . '.png',
					'srcFigurePic' => $wgExtensionAssetsPath .
						'/BlueSpiceAbout/resources/images/' .
						'BlueSpice_Frank_Florian.png',
					'srcWebinarPic' => $wgExtensionAssetsPath .
						'/BlueSpiceAbout/resources/images/' .
						'Webinar_button.png',
					'srcHelpdeskPic' => $wgExtensionAssetsPath .
						'/BlueSpiceAbout/resources/images/' .
						'Helpdesk_button_' . $sLangKey . '.png',
				]
			)
		);
		return true;
	}

}
