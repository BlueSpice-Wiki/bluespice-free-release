( function ( mw ) {
	mw.ext.forms.widget.view.WikiText = function ( cfg ) {
		const classes = [ 'ext-forms-widget-view-wikitext' ];
		cfg.classes = cfg.classes ? cfg.classes.concat( classes ) : classes;
		mw.ext.forms.widget.view.WikiText.parent.call( this, cfg );
		this.loadingText = cfg.loadingText || '...';

		this.wikitext = '';
		this.queue = [];
		this.loading = false;

		if ( cfg.hasOwnProperty( 'wikitext' ) ) {
			this.setValue( cfg.wikitext );
		}
		this.connect( this, {
			parseComplete: function () {
				this.loading = false;
				if ( this.queue.length !== 0 ) {
					this.setValue( this.queue.shift() );
				}
			}
		} );
	};

	OO.inheritClass( mw.ext.forms.widget.view.WikiText, OO.ui.Widget );

	mw.ext.forms.widget.view.WikiText.static.tagName = 'div';

	mw.ext.forms.widget.view.WikiText.prototype.setValue = function ( value ) {
		if ( !value ) {
			return;
		}
		if ( this.loading ) {
			this.queue.push( value );
			return;
		}
		this.wikitext = value;
		this.setLoading();
		this.parse( value ).done( ( response ) => {
			this.setText( response.parse.text[ '*' ] );
			this.emit( 'parseComplete' );
		} ).fail( () => {
			this.setText( this.getParseErrorText() );
			this.emit( 'parseComplete' );
		} );
	};

	mw.ext.forms.widget.view.WikiText.prototype.parse = function ( value ) {
		return new mw.Api().get( {
			action: 'parse',
			text: value,
			contentmodel: 'wikitext'
		} );
	};

	mw.ext.forms.widget.view.WikiText.prototype.setLoading = function () {
		this.setText( new OO.ui.LabelWidget( {
			label: this.loadingText,
			classes: [ 'ext-forms-widget-view-wikitext-loading' ]
		} ).$element );
		this.loading = true;
	};

	mw.ext.forms.widget.view.WikiText.prototype.setText = function ( text ) {
		this.$element.html( text );
	};

	mw.ext.forms.widget.view.WikiText.prototype.getWikitext = function () {
		return this.wikitext;
	};

	mw.ext.forms.widget.view.WikiText.prototype.getParseErrorText = function () {
		return new OO.ui.LabelWidget( {
			label: 'Parse error!',
			classes: [ 'ext-forms-widget-view-wikitext-error' ]
		} ).$element;
	};

}( mediaWiki ) );
