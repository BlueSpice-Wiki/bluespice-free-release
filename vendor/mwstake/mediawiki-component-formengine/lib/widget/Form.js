( function ( mw, $ ) {
	mw.ext.forms.widget.Form = function ( cfg ) {
		mw.ext.forms.widget.Form.parent.call( this, {} );

		this.definitionName = cfg.definitionName;
		if ( !this.definitionName ) {
			return;
		}

		this.forInclusion = cfg.forInclusion || false;
		this.forPreview = cfg.forPreview || false;
		this.action = cfg.action || 'view';
		this.editMode = this.action === 'edit' || this.action === 'create';
		this.definition = cfg.definition || null;
		this.errorReporting = ( !( typeof cfg.errorReporting !== 'undefined' && cfg.errorReporting === false ) );

		// Values for form fields
		this.data = cfg.data || {};
		this.targetPage = cfg.targetPage || null;

		this.createdOn = cfg.createdOn;
		this.skipValidation = false;

		this.layout = new OO.ui.FieldsetLayout( {} );

		mw.ext.forms.mixin.FormLoadingAlert.call( this, false );
		mw.ext.forms.mixin.Message.call( this );
		mw.ext.forms.mixin.BookletLayoutParser.call( this );
		mw.ext.forms.mixin.IndexLayoutParser.call( this );

		if ( this.definition === null ) {
			this.getDefinition( this.definitionName )
				.done( ( definition ) => {
					this.processDefinition( definition );
				} )
				.fail( ( error ) => {
					this.setLoadingVisibility( false );
					this.errorMessage( error, false );
				} );
		} else {
			this.processDefinition( this.definition );
		}

		this.$element.addClass( 'mw-ext-forms-form' );
	};

	OO.inheritClass( mw.ext.forms.widget.Form, OO.ui.Widget );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.DefinitionParser );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.BookletLayoutParser );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.IndexLayoutParser );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.FormLoadingAlert );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.Message );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.ButtonsToolbar );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.EditSummary );

	mw.ext.forms.widget.Form.static.tagName = 'div';

	mw.ext.forms.widget.Form.static.convertToJS = function ( raw ) {
		for ( const key in raw ) {
			if ( !raw.hasOwnProperty( key ) ) {
				continue;
			}
			if ( Array.isArray( raw[ key ] ) ) {
				for ( let i = 0; i < raw[ key ].length; i++ ) {
					mw.ext.forms.widget.Form.static.convertToJS( raw[ key ][ i ] );
				}
				continue;
			}
			let value = raw[ key ];
			if ( typeof value !== 'string' ) {
				if ( $.type( value ) === 'object' ) { // eslint-disable-line no-jquery/no-type
					mw.ext.forms.widget.Form.static.convertToJS( value );
				}
			} else {
				if ( value.startsWith( 'jscb:' ) ) {
					value = value.split( 'jscb:' ).pop().trim();
					// Is a callback
					const re = new RegExp( 'function\\s*([A-z0-9]+)?\\s*\\((?:[^)(]+|\\((?:[^)(]+|\\([^)(]*\\))*\\))*\\)\\s*\\{(?:[^}{]+|\\{(?:[^}{]+|\\{[^}{]*\\})*\\})*\\}', 'gm' ); // eslint-disable-line prefer-regex-literals
					if ( re.test( value ) ) {
						// Function
						raw[ key ] = eval( '(' + value + ')' ); // eslint-disable-line no-eval, security/detect-eval-with-expression
						continue;
					}
					raw[ key ] = mw.ext.forms.widget.Form.static.funcFromString( value );
				} else {
					const re = new RegExp( '(mw\\.message)(.*?)(\\(\\))', 'gm' ); // eslint-disable-line prefer-regex-literals
					if ( re.test( value ) ) {
						// Message
						raw[ key ] = eval( value ); // eslint-disable-line no-eval, security/detect-eval-with-expression
					}
				}

			}
		}
	};

	mw.ext.forms.widget.Form.prototype.processDefinition = function ( definition ) {
		this.definition = definition;
		mw.ext.forms.mixin.DefinitionParser.call( this );
		if ( this.forInclusion && !this.definition.includable ) {
			this.emit(
				'inclusionError',
				mw.message( 'mwstake-formengine-inclusion-error-form-not-includable', this.definitionName ).text()
			);
			return;
		}

		let deps = this.definition.rlDependencies || [];
		if ( typeof deps === 'string' ) {
			deps = [ deps ];
		}
		mw.loader.using(
			deps,
			() => {
				this.parseDefinition().done( ( items ) => {
					this.invokeFormListeners( 'parseComplete', items );
					if ( this.forInclusion ) {
						return;
					}
					if ( this.editMode ) {
						const definitionButtons = definition.buttons;
						if ( !definitionButtons || Array.isArray( definitionButtons ) ) {
							this.buttonsToShow = definitionButtons || [ 'submit', 'reset', this.canCancel() ? 'cancel' : '' ];
							this.buttons = {};
						} else if ( typeof definitionButtons === 'object' ) {
							this.buttonsToShow = Object.keys( definitionButtons );
							this.buttons = definitionButtons;
						}
						if ( definition.hasOwnProperty( 'autosave' ) && definition.autosave !== false ) {
							mw.ext.forms.mixin.Autosave.call( this, definition.autosave );
						}
						mw.ext.forms.mixin.ButtonsToolbar.call( this );
						mw.ext.forms.mixin.EditSummary.call( this );
					}
					this.setLoadingVisibility( false );
					this.$element.append( this.layout.$element );
					this.invokeFormListeners( 'renderComplete' );
					if ( this.editMode ) {
						this.wireFormButtons();
					}
					this.invokeFormListeners( 'initComplete' );
				} );
			},
			() => {
				console.error( 'Failed to load RL dependencies: ' + deps.join( ', ' ) ); // eslint-disable-line no-console
			}
		);
	};

	mw.ext.forms.widget.Form.prototype.getDefinition = function ( defName, defObject, dfd ) {
		defObject = defObject || {};
		dfd = dfd || $.Deferred();

		this.runApiCall( {
			action: 'forms-get-definitions',
			type: 'get-definition',
			name: defName,
			validForTime: this.createdOn
		} ).done( ( response ) => {
			if ( response.success === 0 ) {
				dfd.reject( response.error );
			}

			let definition;
			const lang = response.result.lang;
			let rawDef = response.result.definition;
			if ( lang === 'json' ) {
				rawDef = JSON.parse( rawDef );
				mw.ext.forms.widget.Form.static.convertToJS( rawDef );
				definition = rawDef;
			} else {
				eval( 'definition = function() {' + rawDef + ';}()' ); // eslint-disable-line no-eval, security/detect-eval-with-expression
			}

			if ( typeof definition !== 'object' ) {
				dfd.reject( mw.message( 'mwstake-formengine-error-invalid-form' ).text() );
			}
			if ( definition.hasOwnProperty( 'extends' ) && definition.extends !== '' ) {
				return this.getDefinition( definition.extends, definition, dfd );
			}
			definition = this.mergeDefinitions( defObject, definition );
			dfd.resolve( definition );
		} ).fail( () => {
			dfd.reject( mw.message( 'mwstake-formengine-api-generic-error' ).text() );
		} );

		return dfd.promise();
	};

	/**
	 * Merge two definitions together
	 *
	 * @param {Object} conc
	 * @param {Object} abst
	 * @return {Object}
	 */
	mw.ext.forms.widget.Form.prototype.mergeDefinitions = function ( conc, abst ) {
		let key;
		const result = conc;

		for ( key in abst ) {
			if ( key === 'abstract' ) {
				continue;
			}
			if ( !conc.hasOwnProperty( key ) ) {
				result[ key ] = abst[ key ];
				continue;
			}
			if ( typeof conc[ key ] !== typeof abst[ key ] ) {
				continue;
			}
			if ( Array.isArray( conc[ key ] ) ) {
				if ( typeof abst[ key ] === 'object' && conc[ key ].length === 0 ) {
					result[ key ] = abst[ key ];
					continue;
				}
				result[ key ] = conc[ key ].concat( abst[ key ] );
			} else if ( typeof conc[ key ] === 'object' ) {
				result[ key ] = $.extend( true, conc[ key ], abst[ key ] );
			} else {
				result[ key ] = abst[ key ];
			}
		}
		return result;
	};

	/**
	 * This will allow form to be saved with invalid form
	 * Of course, use this carefully, and make sure fields are otherwise validated
	 *
	 * @param {boolean} skip
	 */
	mw.ext.forms.widget.Form.prototype.setSkipValidation = function ( skip ) {
		this.skipValidation = skip;
	};

	mw.ext.forms.widget.Form.static.funcFromString = function ( cb ) {
		const parts = cb.split( '.' );

		let func = window[ parts[ 0 ] ];
		for ( let i = 1; i < parts.length; i++ ) {
			func = func[ parts[ i ] ];
		}
		return func;
	};

	mw.ext.forms.widget.Form.prototype.runApiCall = function ( data, postIt ) {
		const api = new mw.Api();
		if ( postIt ) {
			data.token = mw.user.tokens.get( 'csrfToken' );
			return api.post( data );
		}
		return api.get( data );
	};

	mw.ext.forms.widget.Form.prototype.wireFormButtons = function () {
		this.connect( this, {
			buttonClick: function ( button ) {
				if ( button === 'submit' ) {
					this.submitForm();
				}
				if ( button === 'reset' ) {
					this.resetForm();
				}
				if ( button === 'cancel' ) {
					this.cancelEdit();
				}
				if ( this.enableProgressSave && button === 'progressSave' ) {
					this.saveProgress();
				}
			}
		} );
	};

	mw.ext.forms.widget.Form.prototype.submitForm = function () {
		if ( this.skipValidation ) {
			this.submitFormValidated();
			return;
		}

		this.validateForm()
			.done( () => {
				this.submitFormValidated();
			} )
			.fail( () => {
				this.invokeFormListeners( 'validationFailed' );
				this.errorMessage( mw.message( 'mwstake-formengine-form-validation-failed' ).text(), true );
			} );
	};

	mw.ext.forms.widget.Form.prototype.submitFormValidated = function () {
		this.clearMessage();
		this.submitButton?.setDisabled( true );
		this.invokeFormListeners( 'submit' )
			.done( () => {
				this.submitDataToTarget();
			} )
			.fail( ( error ) => {
				this.errorMessage( error, true );
			} );
	};

	mw.ext.forms.widget.Form.prototype.saveProgress = function ( silent ) {
		const skipValidationInitial = this.skipValidation,
			progressSavePromise = $.Deferred();
		silent = silent || false;
		this.setSkipValidation( true );
		this.invokeFormListeners( 'progressSave' )
			.done( () => {
				this.submitDataToTarget( true, silent ).done(
					( response ) => {
						progressSavePromise.resolve( response );
					}
				).fail( () => {
					progressSavePromise.reject();
				} );
			} )
			.fail( ( error ) => {
				this.errorMessage( error, true );
				progressSavePromise.reject();
			} );
		this.setSkipValidation( skipValidationInitial );
		return progressSavePromise.promise();
	};

	mw.ext.forms.widget.Form.prototype.getData = function () {

		const dfd = $.Deferred(), data = {};

		const inputs = [];
		for ( const name in this.items.inputs ) {
			if ( !this.items.inputs.hasOwnProperty( name ) ) {
				continue;
			}
			const widget = this.items.inputs[ name ];
			inputs[ name ] = widget;
		}

		if ( this.sealAfterCreation === true ) {
			data._sealed = true;
		}

		this.getDataForItems( inputs, dfd, data );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.getDataForItems = function ( items, dfd, data ) {
		if ( $.isEmptyObject( items ) ) {
			return dfd.resolve( data );
		}
		const name = Object.keys( items )[ 0 ];
		const widget = items[ name ];
		const value = widget.getValue();
		if ( value && typeof value.then === 'function' ) {
			value.done( ( val ) => {
				delete ( items[ name ] );
				data = this.setDataValue( data, name, val );
				this.getDataForItems( items, dfd, data );
			} );
		} else {
			delete ( items[ name ] );
			data = this.setDataValue( data, name, value );
			this.getDataForItems( items, dfd, data );
		}
	};

	mw.ext.forms.widget.Form.prototype.setDataValue = function ( data, name, val ) {
		const nameParts = name.split( '.' );
		let workObject = data;
		for ( let i = 0; i < nameParts.length; i++ ) {
			const part = nameParts[ i ];
			if ( !workObject.hasOwnProperty( part ) || $.type( workObject[ part ] ) !== 'object' ) { // eslint-disable-line no-jquery/no-type
				workObject[ part ] = {};
			}
			if ( i === ( nameParts.length - 1 ) ) {
				workObject[ part ] = val;
			} else {
				workObject = workObject[ part ];
			}
		}
		return data;
	};

	mw.ext.forms.widget.Form.prototype.getSavedDataFor = function ( item ) {
		if ( this.data.hasOwnProperty( item ) ) {
			return this.data[ item ];
		}
		return false;
	};

	mw.ext.forms.widget.Form.prototype.resetForm = function () {
		this.invokeFormListeners( 'reset' );
		for ( const name in this.items.inputs ) {
			if ( !this.items.inputs.hasOwnProperty( name ) ) {
				continue;
			}
			if ( this.data.hasOwnProperty( name ) ) {
				this.items.inputs[ name ].setValue( this.data[ name ] );
			} else {
				this.items.inputs[ name ].setValue();
			}
		}
	};

	mw.ext.forms.widget.Form.prototype.getAction = function () {
		return this.action;
	};

	mw.ext.forms.widget.Form.prototype.canCancel = function () {
		return !!this.targetPage;
	};

	mw.ext.forms.widget.Form.prototype.cancelEdit = function () {
		// Cancel button will show if:
		// - page is in edit mode and valid target for redirect exists
		// - it is explicitly requested in form definition, in which case,
		//   if valid target does not exist, for must handle it itself through listenerss
		if ( !this.editMode ) {
			// For sanity
			return;
		}
		this.invokeFormListeners( 'cancel' ).done( () => {
			if ( this.canCancel() ) {
				window.location = new mw.Title( this.targetPage ).getUrl();
			}

		} );

	};

	// Single listener for now
	mw.ext.forms.widget.Form.prototype.invokeFormListeners = function ( eType, ...params ) {
		const dfd = $.Deferred();
		this.emit( eType, ...params );

		if ( !this.formListeners.hasOwnProperty( eType ) ) {
			dfd.resolve();
			return dfd.promise();
		}
		const func = this.formListeners[ eType ];
		if ( typeof func !== 'function' ) {
			dfd.resolve();
			return dfd.promise();
		}

		const listenerResponse = func.call( this, ...params );
		if ( !listenerResponse || !listenerResponse.hasOwnProperty( 'then' ) || typeof listenerResponse.then !== 'function' ) {
			dfd.resolve( listenerResponse );
		} else {
			listenerResponse
				.done( ( response ) => {
					dfd.resolve( response );
				} )
				.fail( ( error ) => {
					dfd.reject( error );
				} );
		}

		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.validateForm = function ( inputsToValidate ) {
		const dfd = $.Deferred();
		inputsToValidate = inputsToValidate || Object.assign( {}, this.items.inputs );
		this.validateInternally( inputsToValidate, dfd );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.validateInternally = function ( inputs, dfd ) {
		if ( $.isEmptyObject( inputs ) ) {
			return dfd.resolve();
		}
		const keys = Object.keys( inputs );
		const validatingName = keys[ 0 ];
		const validatingInput = inputs[ validatingName ];

		if ( typeof validatingInput.getValidity !== 'function' ) {
			// Some inputs do not support validation - skip over them
			delete ( inputs[ validatingName ] );
			return this.validateInternally( inputs, dfd );
		}

		let inputDfd = $.Deferred();
		if ( typeof validatingInput.validate === 'function' ) {
			// Call validation in context of the form
			const result = validatingInput.validate.call( this, validatingInput.getValue() );
			if ( result && typeof result.promise === 'function' ) {
				inputDfd = result;
			} else {
				if ( result ) {
					inputDfd.resolve();
				} else {
					inputDfd.reject();
				}
			}
		} else {
			inputDfd = validatingInput.getValidity();
		}

		inputDfd.done( () => {
			delete ( inputs[ validatingName ] );
			this.validateInternally( inputs, dfd );
		} )
			.fail( () => {
				validatingInput.focus();
				validatingInput.setValidityFlag( false );
				dfd.reject( validatingName );
			} );
	};

	mw.ext.forms.widget.Form.prototype.submitDataToTarget = function ( saveOnly, silent ) {
		const submitPromise = $.Deferred();
		if ( this.targetPage ) {
			this.target.title = this.targetPage;
		}
		saveOnly = saveOnly || false;
		silent = silent || false;

		this.getData().done( ( data ) => {
			this.invokeFormListeners( 'beforeSubmitData', data ).done( ( processedData ) => {
				processedData = processedData || data;
				let summary = '';
				if ( this.enableEditSummary ) {
					summary = this.summaryInput.getValue();
				}

				if ( this.target === null ) {
					this.invokeFormListeners( 'submitToNullTarget', processedData, summary );
					this.submitButton?.setDisabled( false );
					return submitPromise.resolve( true );
				}
				const apiData = {
					action: 'forms-form-submit',
					form: this.definitionName,
					target: JSON.stringify( this.target ),
					data: JSON.stringify( processedData )
				};
				if ( summary ) {
					apiData.summary = summary;
				}
				this.runApiCall( apiData, true ).done( ( response ) => {
					if ( response.success === 1 ) {
						this.emit( 'submitSuccess' );
						if ( !silent ) {
							this.successMessage( mw.message( 'mwstake-formengine-form-submit-success' ).text(), true );
						}
						if ( !saveOnly ) {
							this.executeAfterAction( response.result, response.defaultAfterAction );
						}
						if ( response.hasOwnProperty( 'result' ) && response.result.hasOwnProperty( 'id' ) ) {
							// This executes on intermediate save.
							// Load the id and set the action to edit, since form instance is no longer new
							this.action = 'edit';
						}
						submitPromise.resolve( response );
					} else {
						if ( !silent ) {
							this.errorMessage( response.error );
						}
						this.emit( 'submitFailed', response.error );
						submitPromise.reject();
					}
					this.submitButton?.setDisabled( false );
				} )
					.fail( ( code, response ) => {
						this.handleMWError( response );
						this.submitButton?.setDisabled( false );
						submitPromise.reject();
					} );
			} ).fail( () => {
				const error = mw.message( 'mwstake-formengine-api-generic-error' ).text();
				this.emit( 'submitFailed', error );
				this.errorMessage( error );
			} );
		} );

		return submitPromise.promise();

	};

	mw.ext.forms.widget.Form.prototype.executeAfterAction = function ( result, defaultAction ) {
		let action = defaultAction || {};
		let isCustom = false;
		if ( this.definition.target.hasOwnProperty( 'afterAction' ) &&
			this.definition.target.afterAction.hasOwnProperty( 'type' ) ) {
			action = this.definition.target.afterAction;
			isCustom = true;
		}
		if ( !action ) {
			return;
		}
		const type = action.type;

		if ( type === 'callback' ) {
			this.doExecuteAfterAction( action, result ).done( ( block ) => {
				if ( isCustom && !block ) {
					this.doExecuteAfterAction( defaultAction, result, false );
				}
			} );
		} else {
			this.doExecuteAfterAction( action, result );
		}
	};

	mw.ext.forms.widget.Form.prototype.doExecuteAfterAction = function ( action, result ) {

		const type = action.type;
		let url;
		let regex;
		let title;
		switch ( type ) {
			case 'redirect':
				if ( !action.hasOwnProperty( 'url' ) ) {
					return;
				}
				url = action.url;
				regex = new RegExp( mw.config.get( 'wgUrlProtocols' ) );
				if ( regex.test( url ) ) {
					window.location = action.url;
					break;
				}
				title = mw.Title.newFromText( url );
				if ( title ) {
					window.location = title.getUrl();
				}
				break;
			case 'callback':
				if ( !action.hasOwnProperty( 'callback' ) ) {
					return;
				}
				return action.callback.call( this, result );
		}
	};

	mw.ext.forms.widget.Form.prototype.handleMWError = function ( response ) {
		const code = response.error.code;
		if ( code === 'badtoken' ) {
			// Session failure - special case
			return this.handleSessionLoss();
		}
		this.errorMessage( response.error.info );
	};

	mw.ext.forms.widget.Form.prototype.handleSessionLoss = function () {
		OO.ui.alert( mw.message( 'mwstake-formengine-session-loss-error' ).text() );

	};
}( mediaWiki, jQuery ) );
