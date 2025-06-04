<?php

namespace BlueSpice\InsertCategory\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.insertcategory.discovery.styles' );
		$this->out->addModules( [
			'ext.bluespice.insertcategory.editor.dialog.bootstrap',
		] );

		if ( $this->getConfig()->get( 'InsertCategoryUploadPanelIntegration' ) ) {
			$this->out->addJsConfigVars( 'bsgInsertCategoryUploadPanelIntegration', true );
		}
	}

}
