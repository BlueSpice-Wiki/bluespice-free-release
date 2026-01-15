( function ( mw, $, undefined ) {
	// This exists because of the bug in OOJS. Since this widget inherits from OO.ui.InputWidget,
	// InputWidget's setValue is called. It will compare old and new values and and update the value
	// if different. This will trigger an update event, which, in turn, will call setValue again
	// in an endless loop. This only happens in FormEditor due to its setup, with change listeners
	// used to rebuild the widget that triggered the event.
	// Bug is that you cannot compare arrays in JS. Input tries to compare old and new array and
	// always gets false. This is called when setting the value to the actual HTML element
	// In case of the Forms, we are not interested in raw HTML element, so this can be skipped
	mw.ext.forms.widget.edit.CheckboxMultiselect = function( cfg ) {
		mw.ext.forms.widget.edit.CheckboxMultiselect.parent.call( this, cfg );
		this.required = cfg.required || false;
		if ( cfg.horizontal ) {
			this.$element.addClass( 'widget-multioption-horizontal' );
		}

		this.checkboxMultiselectWidget.connect( this, {
			select: function() {
				this.emit( 'change', this.getValue() );
			}
		} );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.CheckboxMultiselect, OO.ui.CheckboxMultiselectInputWidget );

	mw.ext.forms.widget.edit.CheckboxMultiselect.prototype.getValue = function() {
		var res = [], selected = this.checkboxMultiselectWidget.findSelectedItems();
		for( var i = 0; i < selected.length; i++ ) {
			res.push( selected[i].getData() );
		}
		return res;
	};

	mw.ext.forms.widget.edit.CheckboxMultiselect.prototype.setValue = function( value ) {
		if ( !this.defaultValue ) {
			this.defaultValue = value;
		}
		value = this.cleanUpValue( value );
		this.checkboxMultiselectWidget.selectItemsByData( value );
		if ( this.optionsDirty ) {
			this.updateOptionsInterface();
		}
		return this;
	};

	mw.ext.forms.widget.edit.CheckboxMultiselect.prototype.getValidity = function() {
		var dfd = $.Deferred();

		if ( this.required && this.getValue().length === 0 ) {
			dfd.reject();
		} else {
			dfd.resolve();
		}

		return dfd.promise();
	};


} )( mediaWiki, jQuery );
