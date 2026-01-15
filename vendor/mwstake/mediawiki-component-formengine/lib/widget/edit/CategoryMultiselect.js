( function ( mw ) {

	/**
	 * This is just for `REL1_31`! In newer version `mw.widgets.CategoryMultiselectWidget` [1]
	 * should be used!
	 *
	 * [1] https://doc.wikimedia.org/mediawiki-core/master/js/#!/api/mw.widgets.CategoryMultiselectWidget
	 *
	 * @param {Object} cfg
	 */
	mw.ext.forms.widget.edit.CategoryMultiselect = function ( cfg ) {
		this.categoryMultiselectWidget = new mw.widgets.CategoryMultiselectWidget( {
			searchTypes: [
				mw.widgets.CategoryMultiselectWidget.SearchType.OpenSearch,
				mw.widgets.CategoryMultiselectWidget.SearchType.InternalSearch
			]
		} );

		mw.ext.forms.widget.edit.CategoryMultiselect.parent.call( this, cfg );

		this.$input.remove();
		this.$element.append( this.categoryMultiselectWidget.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.CategoryMultiselect, OO.ui.InputWidget );

	mw.ext.forms.widget.edit.CategoryMultiselect.prototype.getValue = function () {
		const res = [],
			selected = this.categoryMultiselectWidget.getItems();
		for ( let i = 0; i < selected.length; i++ ) {
			res.push( selected[ i ].getData() );
		}
		return res;
	};

	mw.ext.forms.widget.edit.CategoryMultiselect.prototype.setValue = function ( values ) {
		values = values || [];

		for ( let i = 0; i < values.length; i++ ) {
			const value = values[ i ];
			this.categoryMultiselectWidget.addTag( value, value );
		}

		return this;
	};

	mw.ext.forms.widget.edit.CategoryMultiselect.prototype.setDisabled = function ( isDisabled ) {
		mw.ext.forms.widget.edit.CategoryMultiselect.parent.prototype.setDisabled.call( this, isDisabled );
		this.categoryMultiselectWidget.setDisabled( isDisabled );
		return this;
	};

}( mediaWiki ) );
