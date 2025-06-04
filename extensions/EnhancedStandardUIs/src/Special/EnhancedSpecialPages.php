<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\Special;

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\SpecialPage\UnlistedSpecialPage;

/**
 * Override the default Special:SpecialPages page,
 * based on the code of Special:SpecialPages in MediaWiki core
 *
 * Introduced to hide some special pages for users of pure reader role,
 * and can be extended to add more customizations in the future.
 *
 */

class EnhancedSpecialPages extends UnlistedSpecialPage {

	// Uncategorised/Unused/New/Most images
	// are actually Uncategorised/Unused/New/MostFiles
	public const SPECIAL_PAGES_HIDDEN_FOR_PURE_READER_ROLE = [
		'ImportCSV',
		'ImportSpreadsheet',
		'ImportXML',
		'ViewXML',
		'OAuthConsumerRegistration',
		'OAuthManageConsumers',
		'OAuthListConsumers',
		'Activeusers',
		'AutoblockList',
		'Userrights',
		'Listusers',
		'BlockList',
		'SocialProfiles',
		'WithoutInterwiki',
		'Uncategorizedcategories',
		'Uncategorizedimages',
		'Uncategorizedpages',
		'Uncategorizedtemplates',
		'Unusedcategories',
		'Unusedimages',
		'Unusedtemplates',
		'Wantedcategories',
		'Wantedfiles',
		'Wantedpages',
		'Wantedtemplates',
		'Newimages',
		'Newpages',
		'NewSection',
		'PagesWithProp',
		'Search',
		'TrackingCategories',
		'BotPasswords',
		'Listgrouprights',
		'Listgrants',
		'PasswordPolicies',
		'Log',
		'Tags',
		'Statistics',
		'AllMessages',
		'Activities',
		'UniversalExport',
		'MIMEsearch',
		'FileDuplicateSearch',
		'ApiSandbox',
		'ExpandTemplates',
		'Gadgets',
		'GadgetUsage',
		'PopularPages',
		'DeletePage',
		'Diff',
		'EditPage',
		'PageHistory',
		'PageInfo',
		'PermanentLink',
		'Purge',
		'RandomInCategory',
		'Randomredirect',
		'Randomrootpage',
		'Whatlinkshere',
		'Mostlinkedcategories',
		'Mostimages',
		'Redirect',
		'ComparePages',
		'CreateCategory',
		'CreateForm',
		'CreateProperty',
		'CreateTemplate',
		'Forms',
		'FormStart',
		'RunQuery',
		'Templates',
		'Ask',
		'Concepts',
		'ConstraintErrorList',
		'ExportRDF',
		'GetData',
		'MissingRedirectAnnotations',
		'ProcessingErrorList',
		'Properties',
		'PropertyLabelSimilarity',
		'Types',
		'UnusedProperties',
		'WantedProperties',
		'Allmessages',
		'Protectedpages',
		'Protectedtitles',
		'SpecialProtectPage',
		'ProtectPage'
	];

	public function __construct() {
		parent::__construct( 'EnhancedSpecialPages' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$this->setHeaders();
		$this->outputHeader();
		$out->setPreventClickjacking( false );
		$out->addModuleStyles( 'mediawiki.special' );

		$groups = $this->getPageGroups();

		if ( $groups === false ) {
			return;
		}

		$this->addHelpLink( 'Help:Special pages' );
		$this->outputPageList( $groups );
	}

	/**
	 * Integrate BlueSpice role system
	 * check if user is allowed to view uncustomized special pages list
	 * If the role system is not available, return true
	 * @return bool
	 */
	private function getContentControl() {
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$isAllowed = $permissionManager->userHasRight(
			$this->getUser(),
			'enhancedstandarduis-viewuncustomizedspecialpageslist'
		);
		if ( !class_exists( 'BlueSpice\Permission\Role\Reader' ) ) {
			return true;
		}
		return $isAllowed;
	}

	/**
	 * Sort special pages into groups
	 * modified upon SpecialSpecialpages.php of MediaWiki core
	 * @return array|bool
	 */
	private function getPageGroups() {
		$pages = $this->getSpecialPageFactory()->getUsablePages( $this->getUser() );

		if ( $pages === [] ) {
			// Yeah, that was pointless. Thanks for coming.
			return false;
		}

		if ( !$this->getContentControl() ) {
			foreach ( self::SPECIAL_PAGES_HIDDEN_FOR_PURE_READER_ROLE as $page ) {
				unset( $pages[$page] );
			}
		}

		// Put them into a sortable array
		$groups = [];
		/** @var SpecialPage $page */
		foreach ( $pages as $page ) {
			$group = $page->getFinalGroupName();
			if ( !isset( $groups[$group] ) ) {
				$groups[$group] = [];
			}
			$desc = (string)$page->getDescription();
			$groups[$group][$desc] = [
				$page->getPageTitle(),
				$page->isRestricted(),
				$page->isCached()
			];
		}

		// Sort
		foreach ( $groups as $group => $sortedPages ) {
			ksort( $groups[$group] );
		}

		// Always move "other" to end
		if ( array_key_exists( 'other', $groups ) ) {
			$other = $groups['other'];
			unset( $groups['other'] );
			$groups['other'] = $other;
		}

		return $groups;
	}

	/**
	 * Generate output for the list of special pages
	 * migrated from SpecialSpecialpages.php of MediaWiki core
	 * @param array $groups
	 */
	private function outputPageList( $groups ) {
		$out = $this->getOutput();

		$includesRestrictedPages = false;
		$includesCachedPages = false;

		foreach ( $groups as $group => $sortedPages ) {
			if ( strpos( $group, '/' ) !== false ) {
				list( $group, $subGroup ) = explode( '/', $group, 2 );
				$out->wrapWikiMsg(
					"<h3 class=\"mw-specialpagessubgroup\">$1</h3>\n",
					"specialpages-group-$group-$subGroup"
				);
			} else {
				$out->wrapWikiMsg(
					"<h2 class=\"mw-specialpagesgroup\" id=\"mw-specialpagesgroup-$group\">$1</h2>\n",
					"specialpages-group-$group"
				);
			}
			$out->addHTML(
				Html::openElement( 'div', [ 'class' => 'mw-specialpages-list' ] )
				. '<ul>'
			);
			foreach ( $sortedPages as $desc => [ $title, $restricted, $cached ] ) {
				$pageClasses = [];
				if ( $cached ) {
					$includesCachedPages = true;
					$pageClasses[] = 'mw-specialpagecached';
				}
				if ( $restricted ) {
					$includesRestrictedPages = true;
					$pageClasses[] = 'mw-specialpagerestricted';
				}

				$link = $this->getLinkRenderer()->makeKnownLink( $title, $desc );
				$out->addHTML( Html::rawElement(
						'li',
						[ 'class' => $pageClasses ],
						$link
					) . "\n" );
			}
			$out->addHTML(
				Html::closeElement( 'ul' ) .
				Html::closeElement( 'div' )
			);
		}

		// add legend
		$notes = [];
		if ( $includesRestrictedPages ) {
			$restricedMsg = $this->msg( 'specialpages-note-restricted' );
			if ( !$restricedMsg->isDisabled() ) {
				$notes[] = $restricedMsg->plain();
			}
		}
		if ( $includesCachedPages ) {
			$cachedMsg = $this->msg( 'specialpages-note-cached' );
			if ( !$cachedMsg->isDisabled() ) {
				$notes[] = $cachedMsg->plain();
			}
		}
		if ( $notes !== [] ) {
			$out->wrapWikiMsg(
				"<h2 class=\"mw-specialpages-note-top\">$1</h2>", 'specialpages-note-top'
			);
			$out->wrapWikiTextAsInterface(
				'mw-specialpages-notes',
				implode( "\n", $notes )
			);
		}
	}
}
