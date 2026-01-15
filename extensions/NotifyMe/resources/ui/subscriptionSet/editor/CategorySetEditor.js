ext.notifyme.ui.subscriptionset.editor.CategorySetEditor = function ( cfg ) {
	// Parent constructor
	ext.notifyme.ui.subscriptionset.editor.CategorySetEditor.parent.call( this, cfg );
};

OO.inheritClass( ext.notifyme.ui.subscriptionset.editor.CategorySetEditor, ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor );

ext.notifyme.ui.subscriptionset.editor.CategorySetEditor.prototype.makeLayout = function () {
	this.categoryPicker = new OOJSPlus.ui.widget.CategoryInputWidget( {
		$overlay: this.dialog ? this.dialog.$overlay : true,
		required: true
	} );
	this.categoryPicker.connect( this, {
		change: function () {
			this.emit( 'change', this.getValue() );
		}
	} );
	return new OO.ui.FieldLayout( this.categoryPicker, {
		align: 'top',
		label: mw.message( 'notifyme-ui-subscriptionset-editor-category-label' ).text()
	} );
};

ext.notifyme.ui.subscriptionset.editor.CategorySetEditor.prototype.getValue = function () {
	// STUB
	return {
		category: this.categoryPicker.getValue()
	};
};

ext.notifyme.ui.subscriptionset.editor.CategorySetEditor.prototype.setValue = function ( value ) {
	if ( value && value.hasOwnProperty( 'category' ) ) {
		this.categoryPicker.setValue( value.category );
	}
};

ext.notifyme.ui.subscriptionset.editor.CategorySetEditor.prototype.getValidity = function () {
	return this.categoryPicker.getValidity();
};

ext.notifyme.ui.subscriptionset.editor.CategorySetEditor.prototype.setValidityFlag = function ( valid ) {
	this.categoryPicker.setValidityFlag( valid );
};
