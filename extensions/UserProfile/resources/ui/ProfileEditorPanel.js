ext.userProfile.ui.Editor = function ( cfg ) {
	cfg = cfg || {};
	cfg.padded = true;
	cfg.expanded = false;
	ext.userProfile.ui.Editor.parent.call( this, cfg );

	this.user = cfg.user;
	this.fields = cfg.fields;
	this.data = cfg.data;
};

OO.inheritClass( ext.userProfile.ui.Editor, OO.ui.PanelLayout );

ext.userProfile.ui.Editor.prototype.initialize = function () {
	this.addToolbar();
	this.form = new ext.userProfile.form.Editor( {}, this.fields, this.data );
	this.form.render();
	this.$element.append( this.form.$element );
	this.$element.attr( 'aria-label', mw.msg( 'userprofile-editor-aria-label', this.user ) );

	this.form.connect( this, {
		dataSubmitted: function ( data ) {
			this.save( data );
		}
	} );
	this.form.$element.on( 'lastItemFocusOut', this.focusToolbar.bind( this ) );
};

ext.userProfile.ui.Editor.prototype.addToolbar = function () {
	const editor = this,
		toolFactory = new OO.ui.ToolFactory(),
		toolGroupFactory = new OO.ui.ToolGroupFactory(),
		toolbar = new OO.ui.Toolbar( toolFactory, toolGroupFactory );
	toolbar.$element.addClass( 'user-profile-editor-toolbar' );
	this.$element.append( toolbar.$element );

	const cancelTool = function () {
		cancelTool.super.apply( this, arguments );
	};
	OO.inheritClass( cancelTool, OO.ui.Tool );
	cancelTool.static.name = 'cancel';
	cancelTool.static.icon = 'close';
	cancelTool.static.flags = [];
	cancelTool.static.title = mw.msg( 'userprofile-editor-toolbar-cancel' );
	cancelTool.prototype.onSelect = function () {
		editor.goToProfile();
	};
	cancelTool.prototype.onUpdateState = function () {};
	toolFactory.register( cancelTool );

	const saveTool = function () {
		saveTool.super.apply( this, arguments );
	};
	OO.inheritClass( saveTool, OO.ui.Tool );
	saveTool.static.name = 'save';
	saveTool.static.title = mw.msg( 'userprofile-editor-toolbar-save' );
	saveTool.static.flags = [ 'primary', 'progressive' ];
	saveTool.prototype.onSelect = function () {
		editor.form.submit();
	};
	saveTool.prototype.onUpdateState = function () {};
	toolFactory.register( saveTool );

	toolbar.setup( [
		{
			name: 'cancel',
			type: 'bar',
			include: [ 'cancel' ]
		},
		{
			name: 'actions',
			classes: [ 'actions' ],
			type: 'bar',
			include: [ 'save' ]
		}
	] );
	toolbar.initialize();

	return toolbar;
};

ext.userProfile.ui.Editor.prototype.goToProfile = function () {
	const title = mw.Title.newFromText( mw.config.get( 'wgPageName' ) );
	window.location.href = title.getUrl();
};

ext.userProfile.ui.Editor.prototype.save = function ( data ) {
	$.ajax( {
		method: 'POST',
		url: mw.util.wikiScript( 'rest' ) + '/userprofile/v1/' + this.user,
		data: JSON.stringify( { data: data } ),
		contentType: 'application/json; charset=utf-8',
		dataType: 'json'
	} ).done( ( response ) => { // eslint-disable-line no-unused-vars
		this.goToProfile();
	} ).fail( ( e ) => {
		console.error( e ); // eslint-disable-line no-console
	} );
};

ext.userProfile.ui.Editor.prototype.focusToolbar = function ( e ) {
	e.preventDefault();
	e.stopPropagation();
	const $save = this.$element
		.find( '.user-profile-editor-toolbar' )
		.find( '.oo-ui-barToolGroup-tools' )
		.find( '.oo-ui-tool-name-save' )
		.find( 'a' );
	$save.attr( 'tabindex', 1 );
	$save.trigger( 'focus' );
};
