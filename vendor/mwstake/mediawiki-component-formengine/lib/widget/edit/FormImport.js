( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.FormImport = function( cfg ) {
		mw.ext.forms.widget.edit.FormImport.parent.call( this, cfg );
		this.formName = cfg.formName || '';
		if ( !this.formName )  {
			this.$element.append( mw.msg( 'mwstake-formengine-type-form_import' ) );
			return;
		}

		this.makeLayout();
		this.$element.addClass( 'ext-forms-widget-form-import' );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.FormImport, OO.ui.Widget );

	mw.ext.forms.widget.edit.FormImport.prototype.makeLayout = function() {
		var form = new mw.ext.forms.widget.Form( {
			definitionName: this.formName,
			action: 'edit',
			forInclusion: true,
			forPreview: true,
			data: {}
		} );

		form.connect( this, {
			parseComplete: function( items ) {
				if ( !items ) {
					return;
				}
				this.$element.append( form.layout.$element );
			},
			inclusionError: function( error ) {
				this.$element.append( new OO.ui.LabelWidget( {
					label: error
				} ).$element );
			}
		} );


	};

} )( mediaWiki, jQuery );
