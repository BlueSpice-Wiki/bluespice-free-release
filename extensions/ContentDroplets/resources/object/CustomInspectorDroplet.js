ext.contentdroplets.object.CustomInspectorDroplet = function ( cfg ) {
	ext.contentdroplets.object.CustomInspectorDroplet.parent.call( this, cfg );

	this.registerOverrides();
};

OO.inheritClass( ext.contentdroplets.object.CustomInspectorDroplet,
	ext.contentdroplets.object.Droplet );

ext.contentdroplets.object.CustomInspectorDroplet.prototype.registerOverrides = function () {
	// STUB
};

ext.contentdroplets.object.CustomInspectorDroplet.prototype.updateMWData =
	function ( newData, mwData ) {
		// STUB
		return mwData;
	};

ext.contentdroplets.object.CustomInspectorDroplet.prototype.getForm = function ( data ) {
	const formItems = this.getFormItems();
	formItems.map( ( obj ) => {
		if ( obj.labelAlign ) {
			return obj;
		}
		obj.labelAlign = 'top';
		return obj;
	} );
	const form = new mw.ext.forms.standalone.Form( {
		data: data,
		definition: {
			buttons: [],
			items: [
				{
					type: 'label',
					// eslint-disable-next-line camelcase
					widget_label: this.getDescription()
				}
			].concat( formItems )
		}
	} );

	form.connect( this, {
		initComplete: function ( f ) {
			const inputs = f.getItems().inputs;
			let inputKey;

			for ( inputKey in inputs ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( !inputs.hasOwnProperty( inputKey ) ) {
					continue;
				}
				inputs[ inputKey ].connect( this, {
					change: function () {
						// eslint-disable-next-line no-shadow
						const data = this.modifyFormDataBeforeSubmission( f.getData() );
						form.emit( 'change', data );
					}
				} );
			}
		}
	} );

	return form;
};

ext.contentdroplets.object.CustomInspectorDroplet.prototype.modifyFormDataBeforeSubmission =
	function ( dataPromise ) {
		// This methods receives a promise that resolves with `data` object
		// and returns the same thing
		return dataPromise;
	};

ext.contentdroplets.object.CustomInspectorDroplet.prototype.getFormItems = function () {
	return [];
};

ext.contentdroplets.object.CustomInspectorDroplet.prototype.getClassname = function ( suffix ) {
	suffix = suffix || '';
	return this.getKey().split( /[-_]/ ).map( ( bit ) => bit.charAt( 0 ).toUpperCase() + bit.slice( 1 ) ).join( '' ) + suffix;
};
