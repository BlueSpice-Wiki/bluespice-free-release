<?php

namespace MediaWiki\Extension\UserProfile\Tag;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Extension\UserProfile\Widget\UserProfile;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class UserProfileTagHandler implements ITagHandler {

	/**
	 * @param ProfileManager $profileManager
	 */
	public function __construct(
		private readonly ProfileManager $profileManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		OutputPage::setupOOUI();
		$parser->getOutput()->setEnableOOUI( true );
		$user = $params['user'];
		$parser->getOutput()->addModuleStyles( [ 'ext.userProfile.styles' ] );
		// Parser does have user object available, but its not set on API calls
		$requester = RequestContext::getMain()->getUser();
		$data = $this->profileManager->getProfileData( $user, $requester );
		$availableFields = $this->profileManager->getFieldRegistry()->getSerializedFields(
			$user, $requester, $parser->getContentLanguage()
		);
		// if $validated['fields'], only consider ones listed there
		if ( !empty( $params['fields'] ) ) {
			// Mandatory fields
			$params['fields'][] = 'username';
			$params['fields'][] = 'realName';
			$params['fields'][] = 'userDisplay';
			$params['fields'][] = 'imageUrl';
			$availableFields = array_intersect_key( $availableFields, array_flip( $params['fields'] ) );
		}

		$data['fields'] = $availableFields;
		$widget = new UserProfile( array_merge( [
			'framed' => $params['framed'],
		], $data ) );
		if ( $params['orientation'] === 'vertical' ) {
			$widget->addClasses( [ 'user-profile-vertical' ] );
		}
		return $widget->toString();
	}
}
