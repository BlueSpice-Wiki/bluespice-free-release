<?php

use BlueSpice\InstanceStatus\StatusReport;
use MediaWiki\MediaWikiServices;

return [
	'BlueSpiceInstanceStatus.Report' => static function ( MediaWikiServices $services ) {
		return new StatusReport(
			$services->getObjectFactory(),
			ExtensionRegistry::getInstance()->getAttribute( 'BlueSpiceInstanceStatusInstanceStatusProvider' ),
			ExtensionRegistry::getInstance()->getAttribute( 'BlueSpiceInstanceStatusApiInstanceStatusProvider' ),
		);
	},
];
