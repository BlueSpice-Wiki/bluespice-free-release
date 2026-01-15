( function ( mw, $ ) {
	mw.ext.forms.widget.edit.Pasword = function ( cfg ) {
		if ( !cfg.hasOwnProperty( 'strength' ) ) {
			this.strengthRequired = 1;
		}
		if ( !mw.ext.forms.widget.edit.Pasword.static.strengthMap().hasOwnProperty( cfg.strength ) ) {
			this.strengthRequired = 1;
		}
		this.strengthRequired = mw.ext.forms.widget.edit.Pasword.static.strengthMap()[ cfg.strength ];
		this.currentStrength = null;
		this.required = cfg.required || false;
		this.strongRegex = new RegExp( '(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})' ); // eslint-disable-line prefer-regex-literals
		this.mediumRegex = new RegExp( '((?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{6,}))|((?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9])(?=.{8,}))' ); // eslint-disable-line prefer-regex-literals

		mw.ext.forms.widget.edit.Pasword.parent.call( this, cfg );
		this.main = new OO.ui.TextInputWidget( {
			placeholder: mw.message( 'mwstake-formengine-edit-password-label-password' ).text(),
			type: 'password',
			required: this.required
		} );

		this.main.connect( this, {
			change: 'onChange'
		} );
		this.repeat = new OO.ui.TextInputWidget( {
			placeholder: mw.message( 'mwstake-formengine-edit-password-label-password-re' ).text(),
			type: 'password',
			required: this.required
		} );
		this.repeat.$element.css( { 'margin-top': '10px' } );
		this.strengthGauge = new OO.ui.LabelWidget();
		this.strengthGauge.$element.css( {
			'margin-top': '10px',
			'text-align': 'center',
			color: 'white',
			width: '100%',
			'font-weight': 'bold',
			'border-radius': '5px'
		} );

		this.$input.remove();
		this.$errorPanel = $( '<div>' );
		this.$element.append( this.main.$element, this.$errorPanel, this.repeat.$element, this.strengthGauge.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.Pasword, OO.ui.InputWidget );

	mw.ext.forms.widget.edit.Pasword.static.strengthMap = function () {
		return {
			weak: 1,
			medium: 2,
			strong: 3
		};
	};

	mw.ext.forms.widget.edit.Pasword.static.strengthMessages = function () {
		return {
			2: mw.message( 'mwstake-formengine-edit-password-strength-requirement-medium' ).text(),
			3: mw.message( 'mwstake-formengine-edit-password-strength-requirement-strong' ).text()
		};
	};

	mw.ext.forms.widget.edit.Pasword.prototype.setValue = function ( value ) {
		// NOOP - password field cannot be set
	};

	mw.ext.forms.widget.edit.Pasword.prototype.onChange = function ( value ) {
		this.setErrors( [] );
		if ( !value ) {
			this.strengthGauge.setLabel( '' );
			this.strengthGauge.$element.css( { 'background-color': 'transparent' } );
			this.currentStrength = null;
		}
		if ( this.isStrong( value ) ) {
			this.strengthGauge.setLabel( mw.message( 'mwstake-formengine-edit-password-strength-indicator-stong' ).text() );
			this.strengthGauge.$element.css( { 'background-color': 'green' } );
			this.currentStrength = 3;
		} else if ( this.isMedium( value ) ) {
			this.strengthGauge.setLabel( mw.message( 'mwstake-formengine-edit-password-strength-indicator-medium' ).text() );
			this.strengthGauge.$element.css( { 'background-color': 'orange' } );
			this.currentStrength = 2;
		} else {
			this.strengthGauge.setLabel( mw.message( 'mwstake-formengine-edit-password-strength-indicator-weak' ).text() );
			this.strengthGauge.$element.css( { 'background-color': 'red' } );
			this.currentStrength = 1;
		}
	};

	mw.ext.forms.widget.edit.Pasword.prototype.getValue = function () {
		return this.main.getValue();
	};

	mw.ext.forms.widget.edit.Pasword.prototype.isStrong = function ( value ) {
		return this.strongRegex.test( value );
	};

	mw.ext.forms.widget.edit.Pasword.prototype.isMedium = function ( value ) {
		return this.mediumRegex.test( value );
	};

	mw.ext.forms.widget.edit.Pasword.prototype.setValidityFlag = function ( valid ) {
		this.main.setValidityFlag( valid );
		this.repeat.setValidityFlag( valid );

		if ( !this.passwordsNotEmptyAndMatch() ) {
			this.setErrors( [
				mw.message( 'mwstake-formengine-edit-password-error-mismatch' ).text()
			] );
			return;
		} else if ( this.strengthRequired > 1 && this.strengthRequired > this.currentStrength ) {
			this.setErrors( [
				mw.ext.forms.widget.edit.Pasword.static.strengthMessages()[ this.strengthRequired ]
			] );
		}
	};

	mw.ext.forms.widget.edit.Pasword.prototype.passwordsNotEmptyAndMatch = function () {
		const mainVal = this.main.getValue().trim(),
			repeatVal = this.repeat.getValue().trim();

		return mainVal && repeatVal && mainVal === repeatVal;
	};

	mw.ext.forms.widget.edit.Pasword.prototype.setErrors = function ( errors ) {
		this.$errorPanel.children().remove();
		if ( !errors ) {
			return;
		}
		for ( let i = 0; i < errors.length; i++ ) {
			const widget = new OO.ui.MessageWidget( { type: 'error', inline: true, label: errors[ i ] } );
			this.$errorPanel.append( widget.$element );
		}
	};

	mw.ext.forms.widget.edit.Pasword.prototype.getValidity = function () {
		const dfd = $.Deferred();

		if ( !this.passwordsNotEmptyAndMatch() ) {
			dfd.reject();
		} else if ( this.strengthRequired > 1 && this.strengthRequired > this.currentStrength ) {
			dfd.reject();
		}

		dfd.resolve();

		return dfd.promise();
	};

}( mediaWiki, jQuery ) );
