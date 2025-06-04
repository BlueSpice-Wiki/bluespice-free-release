<?php
/**
 * Avatars Extension for BlueSpice
 *
 * Provide generic and individual user images
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Marc Reymann <reymann@hallowelt.com>
 * @package    BlueSpiceAvatars
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Avatars;

use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class Extension extends \BlueSpice\Extension {

	/**
	 * Clears a user's UserImage setting
	 * @param User $oUser
	 */
	public static function unsetUserImage( $oUser ) {
		/** @var ProfileImageProviderFactory $userOptionsManager */
		$userOptionsManager = MediaWikiServices::getInstance()->getService( 'UserProfile.ImageProviderFactory' );
		foreach ( $userOptionsManager->getAll() as $provider ) {
			if ( $provider instanceof AvatarProvider ) {
				continue;
			}
			$provider->unset( $oUser );
		}
	}
}
