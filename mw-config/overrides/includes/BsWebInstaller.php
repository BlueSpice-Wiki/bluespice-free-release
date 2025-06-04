<?php
/**
 * Core installer web interface.
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
 * @ingroup Installer
 */

require_once MW_INSTALL_PATH . '/mw-config/overrides/includes/BsWebInstallerOutput.php';

use MediaWiki\Installer\WebInstaller;

/**
 * Class for the core installer web interface.
 *
 * @ingroup Installer
 * @since 1.17
 */
class BsWebInstaller extends WebInstaller {

	/**
	 * @inheritDoc
	 */
	public function __construct( WebRequest $request ) {
		$GLOBALS['wgMessagesDirs']['BlueSpiceInstaller'] = __DIR__ . '/../i18n';
		parent::__construct( $request );
		$this->output = new BsWebInstallerOutput( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( array $session ) {
		$isCSS = $this->request->getCheck( 'css' );
		if ( $isCSS ) {
			$this->outputCss();
			return $this->session;
		}

		$html = <<<HTML
The web installer is not supported by BlueSpice.
Please refer to the installation instructions on the
<a href="https://wiki.bluespice.com" title="BlueSpice Helpdesk" target="_blank">
helpdesk
</a>.
HTML;
		$this->output->addHTML( $html );
		$this->finish();
	}

}
