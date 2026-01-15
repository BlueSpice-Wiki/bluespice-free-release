( function ( mw, $, undefined ) {
	mw.ext.forms.widget.Form = function( cfg ) {
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

		if ( null === this.definition ) {
			this.getDefinition( this.definitionName )
				.done( function( definition ) {
					this.processDefinition( definition );
				}.bind( this ) )
				.fail( function( error ) {
					this.setLoadingVisibility( false );
					this.errorMessage( error, false );
				}.bind( this ) );
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

	mw.ext.forms.widget.Form.static.convertToJS = function( raw ) {
		for( var key in raw ) {
			if ( !raw.hasOwnProperty( key ) ) {
				continue;
			}
			if ( Array.isArray( raw[key] ) ) {
				for( var i = 0; i < raw[key].length; i++ ) {
					mw.ext.forms.widget.Form.static.convertToJS( raw[key][i] );
				}
				continue;
			}
			var value = raw[key];
			if ( typeof value !== 'string' ) {
				if ( $.type( value ) === 'object' ) {
					mw.ext.forms.widget.Form.static.convertToJS( value );
				}
			} else {
				if ( value.startsWith( 'jscb:' ) ) {
					value = value.split( 'jscb:' ).pop().trim();
					// Is a callback
					var re = new RegExp( 'function\\s*([A-z0-9]+)?\\s*\\((?:[^)(]+|\\((?:[^)(]+|\\([^)(]*\\))*\\))*\\)\\s*\\{(?:[^}{]+|\\{(?:[^}{]+|\\{[^}{]*\\})*\\})*\\}', 'gm');
					if ( re.test( value ) ) {
						// Function
						raw[key] = eval( "(" + value + ")" );
						continue;
					}
					raw[key] = mw.ext.forms.widget.Form.static.funcFromString( value );
				} else {
					re = new RegExp( '(mw\\.message)(.*?)(\\(\\))', 'gm');
					if ( re.test( value ) ) {
						// Message
						raw[key] = eval( value );
					}
				}

			}
		}
	};

	mw.ext.forms.widget.Form.prototype.processDefinition = function( definition ) {
		this.definition = definition;
		mw.ext.forms.mixin.DefinitionParser.call( this );
		if ( this.forInclusion && !this.definition.includable ) {
			this.emit(
				'inclusionError',
				mw.message( 'mwstake-formengine-inclusion-error-form-not-includable', this.definitionName ).text()
			);
			return;
		}

		var deps = this.definition.rlDependencies || [];
		if ( typeof deps === 'string' ) {
			deps = [ deps ];
		}
		mw.loader.using(
			deps,
			function() {
				this.parseDefinition().done( function( items ) {
					this.invokeFormListeners( 'parseComplete', items );
					if ( this.forInclusion ) {
						return;
					}
					if ( this.editMode ) {
						var definitionButtons = definition.buttons;
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
				}.bind( this ) );
			}.bind( this ),
			function() {
				console.error( 'Failed to load RL dependencies: ' + deps.join( ', ' ) )
			}
		);
	};

	mw.ext.forms.widget.Form.prototype.getDefinition = function( defName, defObject, dfd ) {
		defObject = defObject || {};
		dfd = dfd || $.Deferred();

		this.runApiCall( {
			action: 'forms-get-definitions',
			type: 'get-definition',
			name: defName,
			validForTime: this.createdOn
		} ).done( function ( response )  {
			if ( response.success === 0 ) {
				dfd.reject( response.error );
			}

			var definition;
			var lang = response.result.lang;
			var rawDef = response.result.definition;
			if ( lang === 'json' ) {
				rawDef = JSON.parse( rawDef );
				mw.ext.forms.widget.Form.static.convertToJS( rawDef );
				definition = rawDef;
			} else {
				eval( "definition = function() {" + rawDef + ";}()" );
			}


			if ( typeof definition !== 'object' ) {
				dfd.reject( mw.message( 'mwstake-formengine-error-invalid-form' ).text() );
			}
			if ( definition.hasOwnProperty( 'extends' ) && definition.extends !== '' ) {
				return this.getDefinition( definition.extends, definition, dfd );
			}
			definition = this.mergeDefinitions( defObject, definition );
			dfd.resolve( definition );
		}.bind( this ) ).fail( function( error ) {
			dfd.reject( mw.message( 'mwstake-formengine-api-generic-error' ).text() );
		} );

		return dfd.promise();
	};

	/**
	 * Merge two definitions together
	 *
	 * @param object conc
	 * @param object abst
	 * @returns object
	 */
	mw.ext.forms.widget.Form.prototype.mergeDefinitions = function( conc, abst ) {
		var key, result = conc;

		for( key in abst ) {
			if ( key === 'abstract' ) {
				continue;
			}
			if ( !conc.hasOwnProperty( key ) ) {
				result[key] = abst[key];
				continue;
			}
			if ( typeof conc[key] !== typeof abst[key] ) {
				continue;
			}
			if ( Array.isArray( conc[key] ) ) {
				if ( typeof abst[key] === 'object' && conc[key].length === 0 ) {
					result[key] = abst[key];
					continue;
				}
				result[key] = conc[key].concat( abst[key] );
			} else if ( typeof conc[key] === 'object' ) {
				result[key] = $.extend( true, conc[key], abst[key] );
			} else {
				result[key] = abst[key];
			}
		}
		return result;
	};

	/**
	 * This will allow form to be saved with invalid form
	 * Of course, use this carefully, and make sure fields are otherwise validated
	 *
	 * @param skip
	 */
	mw.ext.forms.widget.Form.prototype.setSkipValidation = function( skip ) {
		this.skipValidation = skip;
	};

	mw.ext.forms.widget.Form.static.funcFromString = function( cb ) {
		var parts = cb.split( '.' );

		var func = window[parts[0]];
		for( var i = 1; i < parts.length; i++ ) {
			func = func[parts[i]];
		}
		return func;
	};

	mw.ext.forms.widget.Form.prototype.runApiCall = function( data, postIt ) {
		var api = new mw.Api();
		if ( postIt ) {
			data.token = mw.user.tokens.get( 'csrfToken' );
			return api.post( data );
		}
		return api.get( data );
	};

	mw.ext.forms.widget.Form.prototype.wireFormButtons = function() {
		this.connect( this, {
			buttonClick: function( button ) {
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

	mw.ext.forms.widget.Form.prototype.submitForm = function() {
		if ( this.skipValidation ) {
			this.submitFormValidated();
			return;
		}

		this.validateForm()
			.done( function() {
				this.submitFormValidated();
			}.bind( this ) )
			.fail( function() {
				this.invokeFormListeners( 'validationFailed' );
				this.errorMessage( mw.message( 'mwstake-formengine-form-validation-failed' ).text(), true );
			}.bind( this ) );
	};

	mw.ext.forms.widget.Form.prototype.submitFormValidated = function() {
		this.clearMessage();
		this.submitButton && this.submitButton.setDisabled( true );
		this.invokeFormListeners( 'submit' )
			.done( function() {
				this.submitDataToTarget();
			}.bind( this ) )
			.fail( function( error ) {
				this.errorMessage( error, true ) ;
			}.bind( this ) );
	};

	mw.ext.forms.widget.Form.prototype.saveProgress = function( silent ) {
		var skipValidationInitial = this.skipValidation,
			progressSavePromise = $.Deferred();
		silent = silent || false;
		this.setSkipValidation( true );
		this.invokeFormListeners( 'progressSave' )
			.done( function() {
				this.submitDataToTarget( true, silent ).done(
					function( response ) {
						progressSavePromise.resolve( response );
					}
				).fail( function() {
					progressSavePromise.reject();
				} );
			}.bind( this ) )
			.fail( function( error ) {
				this.errorMessage( error, true ) ;
				progressSavePromise.reject();
			}.bind( this ) );
		this.setSkipValidation( skipValidationInitial );
		return progressSavePromise.promise();
	};

	mw.ext.forms.widget.Form.prototype.getData = function() {

		var dfd = $.Deferred(), data = {};

		var inputs = [];
		for ( var name in this.items.inputs ) {
			if ( !this.items.inputs.hasOwnProperty( name ) ) {
				continue;
			}
			var widget = this.items.inputs[name];
			inputs[name] = widget;
		}

		if ( this.sealAfterCreation === true ) {
			data._sealed = true;
		}

		this.getDataForItems( inputs, dfd, data );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.getDataForItems = function( items, dfd, data ) {
		if ( $.isEmptyObject( items ) ) {
			return dfd.resolve( data );
		}
		let name = Object.keys( items )[0];
		let widget = items[name];
		let value = widget.getValue();
		if ( value && typeof value.then === 'function' ) {
			value.done( function( val ) {
				delete( items[name] );
				data = this.setDataValue( data, name, val );
				this.getDataForItems( items, dfd, data );
			}.bind( this ) );
		} else {
			delete( items[name] );
			data = this.setDataValue( data, name, value );
			this.getDataForItems( items, dfd, data );
		}
	};

	mw.ext.forms.widget.Form.prototype.setDataValue = function( data, name, val ) {
		var nameParts = name.split( '.' ), workObject = data;
		for( var i = 0; i < nameParts.length; i++ ) {
			var part = nameParts[i];
			if ( !workObject.hasOwnProperty( part ) || $.type( workObject[part] ) !== 'object' ) {
				workObject[part] = {};
			}
			if ( i === ( nameParts.length -1 ) ) {
				workObject[part] = val;
			} else {
				workObject = workObject[part];
			}
		}
		return data;
	};

	mw.ext.forms.widget.Form.prototype.getSavedDataFor = function( item ) {
		if ( this.data.hasOwnProperty( item ) ) {
			return this.data[item];
		}
		return false;
	};

	mw.ext.forms.widget.Form.prototype.resetForm = function() {
		this.invokeFormListeners( 'reset' );
		for ( var name in this.items.inputs ) {
			if ( !this.items.inputs.hasOwnProperty( name ) ) {
				continue;
			}
			if ( this.data.hasOwnProperty( name ) ) {
				this.items.inputs[name].setValue( this.data[name] );
			} else {
				this.items.inputs[name].setValue();
			}
		}
	};

	mw.ext.forms.widget.Form.prototype.getAction = function() {
		return this.action;
	};

	mw.ext.forms.widget.Form.prototype.canCancel = function() {
		return !!this.targetPage;
	};

	mw.ext.forms.widget.Form.prototype.cancelEdit = function() {
		// Cancel button will show if:
		// - page is in edit mode and valid target for redirect exists
		// - it is explicitly requested in form definition, in which case,
		//   if valid target does not exist, for must handle it itself through listenerss
		if ( !this.editMode ) {
			// For sanity
			return;
		}
		this.invokeFormListeners( 'cancel' ).done( function() {
			if ( this.canCancel() ) {
				window.location = new mw.Title( this.targetPage ).getUrl();
			}

		}.bind( this ) );

	};

	// Single listener for now
	mw.ext.forms.widget.Form.prototype.invokeFormListeners = function( eType, ...params ) {
		var dfd = $.Deferred();
		this.emit( eType, ...params );

		if ( !this.formListeners.hasOwnProperty( eType ) ) {
			dfd.resolve();
			return dfd.promise();
		}
		var func = this.formListeners[eType];
		if ( typeof func !== 'function' ) {
			dfd.resolve();
			return dfd.promise();
		}

		var listenerResponse = func.call( this, ...params );
		if ( !listenerResponse || !listenerResponse.hasOwnProperty( 'then' ) || typeof listenerResponse.then !== 'function') {
			dfd.resolve( listenerResponse );
		} else {
			listenerResponse
				.done(function (response) {
					dfd.resolve(response);
				})
				.fail(function (error) {
					dfd.reject(error);
				});
		}

		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.validateForm = function( inputsToValidate ) {
		var dfd = $.Deferred();
		inputsToValidate = inputsToValidate || $.extend( {}, this.items.inputs );
		this.validateInternally( inputsToValidate, dfd );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.validateInternally = function( inputs, dfd ) {
		if ( $.isEmptyObject( inputs ) ) {
			return dfd.resolve();
		}
		var keys = Object.keys( inputs );
		var validatingName = keys[0];
		var validatingInput = inputs[validatingName];

		if ( typeof validatingInput.getValidity !== 'function' ) {
			// Some inputs do not support validation - skip over them
			delete( inputs[validatingName] );
			return this.validateInternally( inputs, dfd );
		}

		var inputDfd = $.Deferred();
		if ( typeof validatingInput.validate === 'function' ) {
			// Call validation in context of the form
			var result = validatingInput.validate.call( this, validatingInput.getValue() );
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

		inputDfd.done( function() {
			delete( inputs[validatingName] );
			this.validateInternally( inputs, dfd );
		}.bind( this ) )
		.fail( function() {
			validatingInput.focus();
			validatingInput.setValidityFlag( false );
			dfd.reject( validatingName );
		} );
	};

	mw.ext.forms.widget.Form.prototype.submitDataToTarget = function( saveOnly, silent ) {
		var submitPromise = $.Deferred();
		if ( this.targetPage ) {
			this.target.title = this.targetPage;
		}
		saveOnly = saveOnly || false;
		silent = silent || false;

		this.getData().done( function( data ) {
			this.invokeFormListeners( 'beforeSubmitData', data ).done( function( processedData ) {
				processedData = processedData || data;
				var summary = '';
				if ( this.enableEditSummary ) {
					summary = this.summaryInput.getValue();
				}

				if ( this.target === null ) {
					this.invokeFormListeners( 'submitToNullTarget', processedData, summary );
					this.submitButton && this.submitButton.setDisabled( false );
					return submitPromise.resolve( true );
				}
				var apiData = {
					action: 'forms-form-submit',
					form: this.definitionName,
					target: JSON.stringify( this.target ),
					data: JSON.stringify( processedData )
				};
				if ( summary ) {
					apiData.summary = summary;
				}
				this.runApiCall( apiData, true ).done( function( response ) {
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
					this.submitButton && this.submitButton.setDisabled( false );
				}.bind( this ) )
					.fail( function( code, response ) {
						this.handleMWError( response );
						this.submitButton && this.submitButton.setDisabled( false );
						submitPromise.reject();
					}.bind( this ) );
			}.bind( this ) ).fail( function() {
				var error = mw.message( 'mwstake-formengine-api-generic-error' ).text();
				this.emit( 'submitFailed', error );
				this.errorMessage( error );
			}.bind( this ) );
		}.bind( this ) );

		return submitPromise.promise();

	};

	mw.ext.forms.widget.Form.prototype.executeAfterAction = function( result, defaultAction ) {
		var action = defaultAction || {};
		var isCustom = false;
		if ( this.definition.target.hasOwnProperty( 'afterAction' ) &&
			this.definition.target.afterAction.hasOwnProperty( 'type' ) ) {
			action = this.definition.target.afterAction;
			isCustom = true;
		}
		if ( !action ) {
			return;
		}
		var type = action.type;

		if ( type === 'callback' ) {
			this.doExecuteAfterAction( action, result ).done( function( block ) {
				if ( isCustom && !block ) {
					this.doExecuteAfterAction( defaultAction, result, false );
				}
			}.bind( this ) );
		} else {
			this.doExecuteAfterAction( action, result );
		}
	};

	mw.ext.forms.widget.Form.prototype.doExecuteAfterAction = function( action, result ) {

		var type = action.type;
		switch( type ) {
			case 'redirect':
				if ( !action.hasOwnProperty( 'url' ) ) {
					return;
				}
				var url = action.url;
				var regex = new RegExp( mw.config.get( 'wgUrlProtocols' ) );
				if ( regex.test( url ) ) {
					window.location = action.url;
					break;
				}
				var title = mw.Title.newFromText( url );
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

	mw.ext.forms.widget.Form.prototype.handleMWError = function( response ) {
		var code = response.error.code;
		if ( code === 'badtoken' ) {
			// Session failure - special case
			return this.handleSessionLoss();
		}
		this.errorMessage( response.error.info );
	};

	mw.ext.forms.widget.Form.prototype.handleSessionLoss = function() {
		OO.ui.alert( mw.message( 'mwstake-formengine-session-loss-error' ).text() );

	};
} )( mediaWiki, jQuery );
