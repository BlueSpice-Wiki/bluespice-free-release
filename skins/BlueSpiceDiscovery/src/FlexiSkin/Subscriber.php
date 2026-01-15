<?php

namespace BlueSpice\Discovery\FlexiSkin;

use MediaWiki\Extension\FlexiSkin\IFlexiSkinSubscriber;

class Subscriber implements IFlexiSkinSubscriber {
	public static function factory() {
		return new static();
	}

	/**
	 * @inheritDoc
	 */
	public function getAffectedRLModules(): array {
		return [
			'skin.discovery.bluespice.themes.default',
			'skin.discovery.bluespice.styles'
		];
	}
}
