ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor = function ( cfg ) {
	// Parent constructor
	ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor.parent.call( this, cfg );
};

OO.inheritClass( ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor, ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor );

ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor.prototype.makeLayout = function () {
	this.namespacePicker = new OOJSPlus.ui.widget.NamespaceInputWidget( {
		$overlay: this.dialog ? this.dialog.$overlay : true,
		required: true
	} );
	this.namespacePicker.connect( this, {
		change: function () {
			this.emit( 'change', this.getValue() );
		}
	} );
	return new OO.ui.FieldLayout( this.namespacePicker, {
		align: 'top',
		label: mw.message( 'notifyme-ui-subscriptionset-editor-namespace-label' ).text()
	} );
};

ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor.prototype.getValue = function () {
	// STUB
	return {
		ns: this.namespacePicker.getValue() || '0'
	};
};

ext.notifyme.ui.subscriptionset.editor.NamespaceSetEditor.prototype.setValue = function ( value ) {
	if ( value && value.hasOwnProperty( 'ns' ) ) {
		this.namespacePicker.setValue( value.ns );
	}
};
