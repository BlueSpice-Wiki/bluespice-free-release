( function ( mw, $ ) {

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget = function ( cfg ) {
		cfg = cfg || {};
		mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.parent.call( this, cfg );
		this.$input = $( '<div>' );
		this.$input.addClass( 'widget-enhanced-title-wikitext' );

		this.titleInput = new mw.widgets.TitleInputWidget( {
			classes: [ 'widget-enhanced-title-wikitext-input-title' ]
		} );
		this.titleInput.connect( this, {
			change: function () {
				this.emit( 'change', this.getValue() );
			}
		} );
		this.$input.append( this.titleInput.$element );
		this.wikitextInput = new OO.ui.TextInputWidget( {
			classes: [ 'widget-enhanced-title-wikitext-input-wikitext' ]
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
			label: mw.message( 'mwstake-formengine-edit-title-wikitext-input-toggle-btn-label' ).text(),
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
		$( this.error.$element ).addClass( 'widget-enhanced-title-wikitext-error' );
		this.error.toggle( false );
		this.$element.append( this.error.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.TitleOrWikitextInputWidget, OO.ui.Widget );

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.prototype.setValue = function ( value ) {
		this.titleInput.setValue( value );
		if ( !this.titleInput.isQueryValid() ) {
			this.wikitextInput.setValue( value );
			this.showWikitextInput();
		} else {
			this.showTitleInput();
		}
	};

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.prototype.showTitleInput = function () {
		this.titleInput.toggle( true );
		this.wikitextInput.toggle( false );
		this.toggleWikitextButton.setValue( false );
	};

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.prototype.showWikitextInput = function () {
		this.titleInput.toggle( false );
		this.wikitextInput.toggle( true );
		this.toggleWikitextButton.setValue( true );
	};

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.prototype.toggleInput = function ( showWikitext ) {
		this.error.toggle( false );
		if ( showWikitext ) {
			const titleValue = this.titleInput.getValue();
			this.wikitextInput.setValue( titleValue );
			this.showWikitextInput();
		} else {
			const wikitextValue = this.wikitextInput.getValue();
			this.titleInput.setValue( wikitextValue );
			if ( !this.titleInput.isQueryValid() ) {
				this.showTitleError();
			}
			this.showTitleInput();
		}
	};

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.prototype.getValue = function () {
		const useWikitext = this.toggleWikitextButton.getValue();
		if ( useWikitext ) {
			return this.wikitextInput.getValue();
		}
		return this.titleInput.getValue();
	};

	mw.ext.forms.widget.edit.TitleOrWikitextInputWidget.prototype.showTitleError = function () {
		this.error.toggle( true );
		this.error.setLabel( mw.message( 'mwstake-formengine-edit-title-wikitext-no-valid-title-error' ).text() );
	};

}( mediaWiki, jQuery ) );
