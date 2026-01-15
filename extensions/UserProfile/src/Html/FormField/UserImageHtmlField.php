<?php

namespace MediaWiki\Extension\UserProfile\Html\FormField;

use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLFormField;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;
use OOUI\ButtonInputWidget;
use OOUI\Widget;

class UserImageHtmlField extends HTMLFormField {
	/**
	 *
	 * @param string $value
	 * @return string
	 */
	public function getInputHTML( $value ) {
		$this->mParent->getOutput()->addModules( [ 'ext.userProfile.profileImage.pref' ] );
		/** @var DynamicFileDispatcherFactory $dfdFactory */
		$dfdFactory = MediaWikiServices::getInstance()->getService( 'MWStake.DynamicFileDispatcher.Factory' );
		$url = $dfdFactory->getUrl( 'userprofileimage', [
			'width' => 128,
			'height' => 128,
		] );
		$img = Html::element( 'img', [
			'src' => $url,
			'alt' => 'userprofile-profile-image-alt'
		] );
		$button = new ButtonInputWidget( [
			'label' => $this->msg( 'userprofile-changeuserimage-title' )->plain(),
			'classes' => [ 'userprofile-userimage-pref-btn' ]
		] );
		return $img . $button;
	}

	/**
	 * Same as getInputHTML, but returns an OOUI object.
	 * Defaults to false, which getOOUI will interpret as "use the HTML version"
	 *
	 * @param string $value
	 * @return Widget|false
	 */
	public function getInputOOUI( $value ) {
		return false;
	}
}
