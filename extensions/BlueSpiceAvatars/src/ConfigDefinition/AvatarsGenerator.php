<?php

namespace BlueSpice\Avatars\ConfigDefinition;

use MediaWiki\HTMLForm\Field\HTMLSelectField;
use MediaWiki\HTMLForm\HTMLFormField;
use MediaWiki\MediaWikiServices;

class AvatarsGenerator extends \BlueSpice\ConfigDefinition\ArraySetting {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_PERSONALISATION . '/BlueSpiceAvatars',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceAvatars/' . static::FEATURE_PERSONALISATION,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceAvatars',
		];
	}

	/**
	 *
	 * @return HTMLFormField
	 */
	public function getHtmlFormField() {
		return new HTMLSelectField( $this->makeFormFieldParams() );
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-avatars-pref-generator';
	}

	/**
	 *
	 * @return array
	 */
	protected function getOptions() {
		$return = [];
		$factory = MediaWikiServices::getInstance()->getService(
			'BSAvatarsAvatarGeneratorFactory'
		);
		foreach ( $factory->getAllGenerators() as $generator ) {
			// TODO: generators should return a label for ui
			$return[$generator->getName()] = $generator->getName();
		}
		return $return;
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-avatars-pref-generator-help';
	}
}
