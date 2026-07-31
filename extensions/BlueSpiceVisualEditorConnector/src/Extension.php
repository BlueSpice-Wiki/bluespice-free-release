<?php

namespace BlueSpice\VisualEditorConnector;

class Extension extends \BlueSpice\Extension {
	public static function onRegistration() {
		if ( isset( $GLOBALS[ 'wgVisualEditorPreloadModules' ] ) ) {
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.tags";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.softHyphen";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.lineBreak";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.styleInspector";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.textAlignment";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.textIndentation";
			$GLOBALS[ 'wgVisualEditorPreloadModules' ][] = "ext.bluespice.visualEditorConnector.internalLink";
		}
	}
}
