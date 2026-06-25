( function ( mw, $ ) {

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget = function ( cfg ) {
		cfg = cfg || {};
		mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.parent.call( this, cfg );
		this.$input = $( '<div>' );
		this.$input.addClass( 'widget-enhanced-number-wikitext' );

		this.numberInput = new OO.ui.NumberInputWidget( {
			classes: [ 'widget-enhanced-number-wikitext-input-title' ]
		} );
		this.numberInput.connect( this, {
			change: function () {
				this.error.toggle( false );
				this.emit( 'change', this.getValue() );
			}
		} );
		this.$input.append( this.numberInput.$element );
		this.wikitextInput = new OO.ui.TextInputWidget( {
			classes: [ 'widget-enhanced-number-wikitext-input-wikitext' ]
		} );
		this.wikitextInput.connect( this, {
			change: function () {
				this.emit( 'change', this.getValue() );
			}
		} );
		this.wikitextInput.toggle( false );
		this.$input.append( this.wikitextInput.$element );
		this.toggleWikitextButton = new OO.ui.ToggleButtonWidget( {
			icon: 'wikiText',
			label: mw.message( 'mwstake-formengine-edit-number-wikitext-input-toggle-btn-label' ).text(),
			invisibleLabel: true,
			value: false
		} );
		this.toggleWikitextButton.connect( this, {
			change: 'toggleInput'
		} );
		this.$input.append( this.toggleWikitextButton.$element );
		this.$element.append( this.$input );

		this.error = new OO.ui.MessageWidget( {
			type: 'error',
			inline: true
		} );
		$( this.error.$element ).addClass( 'widget-enhanced-number-wikitext-error' );
		this.error.toggle( false );
		this.$element.append( this.error.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.NumberOrWikitextInputWidget, OO.ui.Widget );

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.setValue = function ( value ) {
		if ( !this.isNumeric( value ) ) {
			this.wikitextInput.setValue( value );
			this.showWikitextInput();
		} else {
			this.numberInput.setValue( value );
			this.showNumberInput();
		}
	};

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.showNumberInput = function () {
		this.numberInput.toggle( true );
		this.wikitextInput.toggle( false );
		this.toggleWikitextButton.setValue( false );
	};

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.showWikitextInput = function () {
		this.numberInput.toggle( false );
		this.wikitextInput.toggle( true );
		this.toggleWikitextButton.setValue( true );
	};

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.toggleInput = function ( showWikitext ) {
		this.error.toggle( false );
		if ( showWikitext ) {
			const numberValue = this.numberInput.getValue();
			if ( numberValue !== '' ) {
				this.wikitextInput.setValue( numberValue );
			}
			this.showWikitextInput();
		} else {
			const wikitextValue = this.wikitextInput.getValue();
			if ( !this.isNumeric( wikitextValue ) ) {
				this.showNumberError();
			} else {
				this.numberInput.setValue( wikitextValue );
			}
			this.showNumberInput();
		}
	};

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.getValue = function () {
		const useWikitext = this.toggleWikitextButton.getValue();
		if ( useWikitext ) {
			return this.wikitextInput.getValue();
		}
		return this.numberInput.getValue();
	};

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.showNumberError = function () {
		this.error.toggle( true );
		this.error.setLabel( mw.message( 'mwstake-formengine-edit-number-wikitext-no-valid-number-error' ).text() );
	};

	mw.ext.forms.widget.edit.NumberOrWikitextInputWidget.prototype.isNumeric = function ( value ) {
		return typeof value === 'number' ?
			!Number.isNaN( value ) :
			typeof value === 'string' && value.trim() !== '""' && !Number.isNaN( Number( value ) );
	};

}( mediaWiki, jQuery ) );
