<?php

namespace MWStake\MediaWiki\Component\FormEngine;

use MediaWiki\ResourceLoader\FileModule as ResourceLoaderFileModule;

class FormElementModule extends ResourceLoaderFileModule {
	public function __construct() {
		$options['localBasePath'] = dirname( __DIR__ ) . '/lib';
		$externalElements = $GLOBALS['mwsgFormEngineElementModules'];
		if ( !is_array( $externalElements ) ) {
			$externalElements = [];
		}
		$options['dependencies'] = array_merge( [ 'ext.forms.widgets' ], $externalElements );
		parent::__construct( $options );
	}
}
