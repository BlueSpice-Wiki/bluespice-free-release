<?php

require_once MW_INSTALL_PATH . '/mw-config/overrides/includes/BsEdition.php';

use BlueSpice\Installer\BsEdition;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Installer\CliInstaller;
use MediaWiki\Installer\DatabaseInstaller;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class BsCliInstaller extends CliInstaller {

	/**
	 * Selected BlueSpice edition
	 *
	 * @var string
	 */
	private $edition = 'free';

	/**
	 * @inheritDoc
	 */
	public function __construct( $siteName, $admin = null, array $options = [] ) {
		$GLOBALS['wgMessagesDirs']['BlueSpiceInstaller'] = __DIR__ . '/../i18n';
		parent::__construct( $siteName, $admin, $options );

		$blueSpiceEdition = new BsEdition();
		$this->edition = $blueSpiceEdition->getEdition();
	}

	/**
	 * @inheritDoc
	 */
	protected function getInstallSteps( DatabaseInstaller $installer ) {
		$installSteps = parent::getInstallSteps( $installer );
		$bsInstallSteps = [
			[ 'name' => 'sidebar', 'callback' => [ $this, 'createSidebar' ] ]
		];

		// Add BlueSpice install steps to the core install list,
		// then adding any callbacks that wanted to attach after a given step
		foreach ( $bsInstallSteps as $step ) {
			$installSteps[] = $step;
			if ( isset( $this->extraInstallSteps[$step['name']] ) ) {
				$installSteps = array_merge(
					$installSteps,
					$this->extraInstallSteps[$step['name']]
				);
			}
		}

		return $installSteps;
	}

	/**
	 * We don't want to have any extensions installed by default,
	 * as we require update.php to be run anyways.
	 * This avoids issues with several extensions, like SemanticMediaWiki and OATHAuth
	 *
	 * @inheritDoc
	 */
	public function getVar( $name, $default = null ) {
		if ( strpos( $name, 'ext-' ) === 0 ) {
			$this->setVar( $name, '0' );
		}
		if ( $name === '_Extensions' ) {
			return [];
		}

		return parent::getVar( $name, $default );
	}

	/**
	 * @inheritDoc
	 */
	protected function includeExtensions() {
		// Don't load extensions
		return Status::newGood();
	}

	/**
	 * Insert Main Page with default content.
	 *
	 * @param DatabaseInstaller $installer
	 * @return Status
	 */
	protected function createMainpage( DatabaseInstaller $installer ) {
		$status = Status::newGood();
		$title = Title::newMainPage();
		if ( $title->exists() ) {
			$status->warning( 'config-install-mainpage-exists' );
			return $status;
		}
		try {
			$path = dirname( __DIR__ ) . '/content/mainpage/' . $this->edition . '.html';
			echo "$path\n";

			$rawContent = file_get_contents( $path );
			$processedContent = preg_replace_callback(
				'#\{\{int:(.*?)\}\}#si',
				static function ( $matches ) {
					return wfMessage( $matches[1] )->inContentLanguage()->text();
				},
				$rawContent
			);
			$content = new WikitextContent( $processedContent );
			$page = MediaWikiServices::getInstance()->getWikiPageFactory()
				->newFromTitle( $title );
			$user = User::newSystemUser( 'BlueSpice default' );

			$updater = $page->newPageUpdater( $user );
			$updater->setContent( SlotRecord::MAIN, $content );
			$comment = CommentStoreComment::newUnsavedComment( '' );
			$updater->saveRevision( $comment, EDIT_NEW );
			$status = $updater->getStatus();
		} catch ( Exception $e ) {
			// using raw, because $wgShowExceptionDetails can not be set yet
			$status->fatal( 'config-install-mainpage-failed', $e->getMessage() );
		}

		return $status;
	}

	/**
	 * Sidebar with BlueSpice content
	 *
	 * @param DatabaseInstaller $installer
	 * @return Status
	 */
	protected function createSidebar( DatabaseInstaller $installer ) {
		$status = Status::newGood();
		$title = Title::makeTitleSafe( NS_MEDIAWIKI, 'Sidebar' );
		if ( $title->exists() ) {
			$status->warning( 'config-install-sidebar-exists' );
			return $status;
		}
		try {
			$path = dirname( __DIR__ ) . '/content/sidebar/' . $this->edition . '.wikitext';
			echo "$path\n";

			$rawContent = file_get_contents( $path );
			$content = new WikitextContent( $rawContent );
			$page = MediaWikiServices::getInstance()->getWikiPageFactory()
				->newFromTitle( $title );
			$user = User::newSystemUser( 'BlueSpice default' );

			$updater = $page->newPageUpdater( $user );
			$updater->setContent( SlotRecord::MAIN, $content );
			$comment = CommentStoreComment::newUnsavedComment( '' );
			$updater->saveRevision( $comment, EDIT_NEW );
			$status = $updater->getStatus();
		} catch ( Exception $e ) {
			// using raw, because $wgShowExceptionDetails can not be set yet
			$status->fatal( 'config-install-sidebar-failed', $e->getMessage() );
		}

		return $status;
	}
}
