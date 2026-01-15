( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.Multiplier = function( cfg ) {
		mw.ext.forms.widget.edit.Multiplier.parent.call( this, cfg );

		this.base = cfg.base || [];
		if ( !Array.isArray( this.base ) ) {
			this.base = [ this.base ];
		}

		this.addInitially = cfg.addInitially || false;
		this.max = cfg.max || -1;
		this.timesAdded = 0;
		this.form = cfg.form;
		this.label = cfg.label || '';
		this.addNewLabel = cfg.addNewLabel || 'Add new';
		this.returnType = cfg.returnType || mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_ARRAY;
		this.returnSeparator = cfg.returnSeparator || ',';
		this.returnKey = cfg.returnKey || false;
		if ( !this.returnKey && this.returnType === mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_OBJECT ) {
			this.returnType = mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_ARRAY;
		}
		this.wrapInHorizontal = cfg.wrapInHorizontal || false;

		this.items = {};
		this.layouts = {};

		this.makeLayout();
		if ( cfg.addInitially ) {
			this.addItem( this.base );
		}

		this.$input.remove();
		this.$element.addClass( 'ext-forms-widget-multiplier' );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.Multiplier, OO.ui.InputWidget );

	mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_OBJECT = 'object';
	mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_ARRAY = 'array';
	mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_SIMPLE_ARRAY = 'simple_array';
	mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_STRING = 'string';

	mw.ext.forms.widget.edit.Multiplier.prototype.makeLayout = function() {
		this.itemsLayout = new OO.ui.FieldsetLayout( {
			label: this.label,
			classes: [ 'multiplier-items-layout' ]
		} );
		this.addButton = new OO.ui.ButtonWidget( {
			framed: false,
			label: this.addNewLabel
		} );
		this.addButton.connect( this, {
			click: 'addItem'
		} );

		this.$element.append(
			this.itemsLayout.$element,
			new OO.ui.HorizontalLayout( {
				items: [
					this.addButton
				]
			} ).$element
		);
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.addItem = function( item ) {
		item = item || this.base;
		var itemLayout = new OO.ui.FieldsetLayout( {
			data: {},
			classes: [ 'multiplier-item' ]
		} );
		if ( this.wrapInHorizontal ) {
			itemLayout = new OO.ui.HorizontalLayout( {
				classes: [ 'multiplier-item' ]
			} );
		}
		var parsedItems = this.form.parseItems( item, itemLayout, true );
		this.attachChangeListeners( parsedItems );
		var itemName =  this.form.getRandomName( 'multiplier', Math.floor( Math.random() * (9999 - 100) + 100) );
		this.items[itemName] = parsedItems;
		this.timesAdded++;
		if ( parseInt( this.max ) === this.timesAdded ) {
			this.addButton.setDisabled( true );
		}
		var removeButton = new OO.ui.ButtonWidget( {
			icon: 'trash',
			framed: false,
			title: mw.message( 'mwstake-formengine-title-remove' ).text(),
			classes: [ 'multiplier-remove-btn' ],
			data: {
				item_name: itemName
			}
		} );
		var multiplier = this;
		removeButton.connect( removeButton, {
			click: function() {
				var name = this.getData().item_name;
				this.$element.remove();
				multiplier.removeItem( name );
			}
		} );
		this.layouts[itemName] = itemLayout;
		this.itemsLayout.addItems( [ removeButton, itemLayout ] );
		this.emit( 'change', this.items, this.layouts );
		return parsedItems;
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.removeItem = function( name ) {
		if ( !this.layouts.hasOwnProperty( name ) ) {
			return;
		}
		this.layouts[name].$element.remove();
		delete( this.layouts[name] );
		delete( this.items[name] );
		this.emit( 'change', this.items );
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.setValue = function( value ) {
		this.layout = {};
		this.items = {};
		if ( this.itemsLayout ) {
			this.itemsLayout.clearItems();
		}

		return this.returnType === mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_OBJECT ?
			this.setValueObject( value ) :
			this.setValueArray( value );
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.setValueArray = function( value ) {
		var inputName;

		if ( !value || !Array.isArray( value ) ) {
			return;
		}

		for( var i = 0; i < value.length; i++ ) {
			var addedItems = this.addItem();
			if ( this.returnType === mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_SIMPLE_ARRAY  ) {
				for( inputName in addedItems ) {
					if ( addedItems[inputName] instanceof OO.ui.InputWidget ) {
						addedItems[inputName].setValue( value[i] );
						break;
					}
				}
			} else {
				for( inputName in value[i] ) {
					if ( addedItems.hasOwnProperty( inputName ) && addedItems[inputName] instanceof OO.ui.InputWidget ) {
						addedItems[inputName].setValue( value[i][inputName] );
					}
				}
			}
		}
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.setValueObject = function( value ) {
		if ( !value || typeof value !== 'object' ) {
			return;
		}

		for( var key in value ) {
			var addedItems = this.addItem();
			value[key][this.returnKey] = key;
			for( var name in value[key] ) {
				if ( addedItems.hasOwnProperty( name ) && addedItems[name] instanceof OO.ui.InputWidget ) {
					addedItems[name].setValue( value[key][name] );
				}
			}
		}
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.attachChangeListeners = function( items ) {
		for( var name in items ) {
			if ( items[name] instanceof OO.ui.InputWidget ) {
				items[name].connect( this, {
					change: function() {
						this.emit( 'change', this.items, this.layouts );
					}
				} );
			}
		}
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.getValidity = function() {
		var dfd = $.Deferred();
		var toCheck = {};
		for( var name in this.items ) {
			for( var itemName in this.items[name] ) {
				if ( !this.items[name][itemName] instanceof OO.ui.InputWidget ) {
					continue;
				}
				toCheck[itemName] = this.items[name][itemName];
			}
		}

		this.form.validateInternally( toCheck, dfd );
		return dfd.promise();
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.setValidityFlag = function( valid ) {
		if ( !valid ) {
			this.$element.addClass( 'input-invalid' );
		} else {
			this.$element.removeClass( 'input-invalid' );
		}
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.getValue = function() {
		switch ( this.returnType ) {
			case mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_OBJECT:
				return this.returnObject();
			case mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_STRING:
				return this.returnString();
			default:
				return this.returnArray();
		}
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.returnObject = function() {
		var result = {};
		for( var name in this.items ) {
			var singleResult = {};
			for( var itemName in this.items[name] ) {
				if ( ! ( this.items[name][itemName] instanceof OO.ui.InputWidget ) ) {
					continue;
				}
				if ( itemName === this.returnKey ) {
					continue;
				}
				singleResult[itemName] = this.items[name][itemName].getValue();
			}
			result[this.items[name][this.returnKey].getValue()] = singleResult;
		}
		return result;
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.returnArray = function() {
		var result = [];
		for( var name in this.items ) {
			var singleResult = {};
			for( var itemName in this.items[name] ) {
				if ( this.items[name][itemName] instanceof OO.ui.InputWidget ) {
					if ( this.returnType === mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_SIMPLE_ARRAY ) {
						singleResult = this.items[name][itemName].getValue();
						break;
					}
					singleResult[itemName] = this.items[name][itemName].getValue();
				}
			}
			result.push( singleResult );
		}
		return result;
	};

	mw.ext.forms.widget.edit.Multiplier.prototype.returnString = function() {
		var origReturnType = this.returnType;
		this.returnType = mw.ext.forms.widget.edit.Multiplier.static.RETURN_TYPE_SIMPLE_ARRAY;
		var result = this.returnArray();
		this.returnType = origReturnType;
		return result.join( this.returnSeparator );
	};

} )( mediaWiki, jQuery );
