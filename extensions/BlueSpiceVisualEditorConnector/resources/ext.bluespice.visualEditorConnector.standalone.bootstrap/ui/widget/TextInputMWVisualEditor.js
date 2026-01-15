bs.util.registerNamespace( 'bs.ui.widget' );

bs.ui.widget.TextInputMWVisualEditor = function ( config ) {
	bs.ui.widget.TextInputMWVisualEditor.parent.call( this, config );

	this.selector = config.selector || '.bs-vec-widget';
	this.visualEditor = null;
	this.config = config;
	this.loading = false;
	this.currentValue = config.value || '';
	this.bsVecConfig = require( './config.json' );
};

OO.inheritClass( bs.ui.widget.TextInputMWVisualEditor, OO.ui.MultilineTextInputWidget );

bs.ui.widget.TextInputMWVisualEditor.prototype.onFocus = function () {
	if ( this.loading || this.visualEditor ) {
		return;
	}

	this.makeVisualEditor( this.config );
	$( this.config.selector ).hide();
	$( this.config.selector ).attr( 'tabindex', '-1' );
};

/**
 * @return {string}
 */
bs.ui.widget.TextInputMWVisualEditor.prototype.getValue = function () {
	if ( !this.visualEditor ) {
		return this.currentValue;
	}
	const promise = this.visualEditor.getWikiText();
	promise.done( ( value ) => {
		// keep track of actual value anyway to be on the safe side
		this.currentValue = value;
	} );
	return promise;
};

/**
 * @param {string} value
 * @return {undefined}
 */
bs.ui.widget.TextInputMWVisualEditor.prototype.setValue = function ( value ) {
	if ( !this.visualEditor ) {
		this.currentValue = value;
		return;
	}

	this.visualEditor.clearSurfaces();
	this.visualEditor.addSurface(
		ve.dm.converter.getModelFromDom(
			ve.createDocumentFromHtml( value )
		)
	);
};

bs.ui.widget.TextInputMWVisualEditor.prototype.makeVisualEditor = function ( config ) {
	config = config || this.config;

	this.loading = true;
	const modules = [
		'ext.bluespice.visualEditorConnector.standalone'
	].concat( this.bsVecConfig.StandalonePluginModules || [] );

	mw.loader.using( modules ).done( () => {
		this.emit( 'editorStartup', this );
		bs.vec.createEditor( config.id, {
			renderTo: config.selector,
			value: this.currentValue,
			format: config.format
		} ).done( ( target ) => {
			this.visualEditor = target;
			this.visualEditor.getSurface().getModel().on( 'history', this.onHistoryChange, [], this );
		} ).then( () => {
			this.emit( 'editorStartupComplete', this );
			this.loading = false;
		} );
	} );
};

bs.ui.widget.TextInputMWVisualEditor.prototype.onHistoryChange = function () {
	// we do not need to track the actual value, just that there was a change
	this.emit( 'change', this.currentValue );
};

bs.ui.widget.TextInputMWVisualEditor.prototype.getValidity = function () {
	// visual editor is always valid
	const $dfd = $.Deferred();
	$dfd.resolve();
	return $dfd.promise( this );
};
