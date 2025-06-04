<?php
/**
 * BlueSpice Output handler for the web installer.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Deployment
 */

use MediaWiki\Installer\WebInstallerOutput;

/**
 * BlueSpice output class modelled on OutputPage.
 *
 * @ingroup Deployment
 * @since 2.27
 *
 * @author Stephan Muggli
 * @author Robert Vogel <vogel@hallowelt.com>
 */
class BsWebInstallerOutput extends WebInstallerOutput {

	/**
	 * @inheritDoc
	 */
	public function outputTitle() {
		$bsVersion = file_get_contents( MW_INSTALL_PATH . '/BLUESPICE-VERSION' );
		$bsVersion = trim( $bsVersion );
		echo wfMessage( 'bs-installer-title', MW_VERSION, $bsVersion )->plain();
	}

	/**
	 *
	 * @return void
	 */
	public function getCSS() {
		$sInlineCSS = parent::getCSS();
		$sInlineCSS .= file_get_contents( dirname( __DIR__ ) . '/resources/main.css' );
		return $sInlineCSS;
	}

	/**
	 *
	 * @return void
	 */
	public function outputFooter() {
		echo Html::closeElement( 'body' ) . Html::closeElement( 'html' );
	}
}
