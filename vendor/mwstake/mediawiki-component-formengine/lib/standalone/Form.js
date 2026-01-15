( function ( mw, $) {
	mw.ext.forms.standalone.Form = function( cfg ) {
		mw.ext.forms.standalone.Form.parent.call( this, {} );
		OO.EventEmitter.call( this );

		this.config = cfg || {};
		if ( $.isEmptyObject( this.config ) ) {
			// Someone inherits this
			this.render();
		}
	};

	OO.inheritClass( mw.ext.forms.standalone.Form, OO.ui.Widget );
	OO.mixinClass( mw.ext.forms.standalone.Form, OO.EventEmitter );

	mw.ext.forms.standalone.Form.static.name = 'standalone-form';

	mw.ext.forms.standalone.Form.prototype.render = function() {
		if ( !this.config.definition.hasOwnProperty( 'items' ) ) {
			this.config.definition.items = this.makeItems();
		}

		this.createForm();
	};

	mw.ext.forms.standalone.Form.prototype.createForm = function() {
		var standalone = this;

		this.form = new mw.ext.forms.widget.Form( $.extend( true, {}, {
			definitionName: mw.ext.forms.standalone.Form.static.name,
			action: this.config.hasOwnProperty( 'data' ) ? 'edit' : 'create',
		}, this.config, {
			// Force certain properties
			definition: {
				target: null,
				listeners: $.extend( true, {}, {
					setValueFor: function( name, value, dataset ) {
						standalone.onSetValueFor( this, name, value, dataset );
					},
					parseComplete: function( items ) {
						standalone.onParseComplete( this, items );
					},
					renderComplete: function() {
						standalone.onRenderComplete( this );
					},
					initComplete: function() {
						standalone.onInitComplete( this );
					},
					submit: function() {
						return standalone.onSubmit( this );
					},
					submitToNullTarget: function( data, summary ) {
						standalone.onDataSubmitted( data, summary );
					},
					reset: function() {
						standalone.onReset( this );
					},
					cancel: function() {
						return standalone.onCancel( this );
					},
					progressSave: function() {
						return standalone.onProgressSave( this );
					},
					beforeSubmitData: function( data ) {
						return standalone.onBeforeSubmitData( this, data );
					},
					validationFailed: function() {
						standalone.onValidationFailed( this );
					}
				}, this.config.listeners || {} )
			},
			forInclusion: false,
			forPreview: false
		} ) );

		this.$element = this.form.$element;
		this.$element.addClass( 'ext-forms-standalone-form' );
	};

	mw.ext.forms.standalone.Form.prototype.makeItems = function() {
		return [];
	};

	mw.ext.forms.standalone.Form.prototype.getForm = function() {
		return this.form;
	};

	mw.ext.forms.standalone.Form.prototype.submit = function() {
		return this.form.submitForm();
	};

	mw.ext.forms.standalone.Form.prototype.onSetValueFor = function( form, name, value, dataset ) {
		this.emit( 'setValueFor', form, name, value, dataset );
	};

	mw.ext.forms.standalone.Form.prototype.onParseComplete = function( form, items ) {
		this.emit( 'parseComplete', form, items );
	};

	mw.ext.forms.standalone.Form.prototype.onRenderComplete = function( form ) {
		this.emit( 'renderComplete', form );
	};

	mw.ext.forms.standalone.Form.prototype.onInitComplete = function( form ) {
		this.emit( 'initComplete', form );
	};

	mw.ext.forms.standalone.Form.prototype.onSubmit = function( form ) {
		return this.resolvedDeferrable();
	};

	mw.ext.forms.standalone.Form.prototype.onReset = function( form ) {
		this.emit( 'reset', form );
	};

	mw.ext.forms.standalone.Form.prototype.onCancel = function( form ) {
		this.emit( 'cancel', form );
		return this.resolvedDeferrable();
	};

	mw.ext.forms.standalone.Form.prototype.onProgressSave = function( form ) {
		this.emit( 'progressSave', form );
		return this.resolvedDeferrable();
	};

	mw.ext.forms.standalone.Form.prototype.onBeforeSubmitData = function( form, data ) {
		this.emit( 'beforeSubmitData', form );
		return this.resolvedDeferrable( data );
	};

	mw.ext.forms.standalone.Form.prototype.onDataSubmitted = function( data, summary ) {
		this.emit( 'dataSubmitted', data, summary );
	};

	mw.ext.forms.standalone.Form.prototype.onValidationFailed = function( form ) {
		this.emit( 'validationFailed' );
	};

	mw.ext.forms.standalone.Form.prototype.resolvedDeferrable = function( data ) {
		data = data || null;
		var dfd = $.Deferred();
		if ( data !== null ) {
			dfd.resolve( data );
		} else {
			dfd.resolve();
		}
		return dfd.promise();
	};

} )( mediaWiki, jQuery );
