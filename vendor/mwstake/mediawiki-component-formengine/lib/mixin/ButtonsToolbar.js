( function ( mw ) {
	mw.ext.forms.mixin.ButtonsToolbar = function () {

		const toolFactory = new OO.ui.ToolFactory(),
			toolGroupFactory = new OO.ui.ToolGroupFactory(),
			toolbar = new OO.ui.Toolbar( toolFactory, toolGroupFactory );
		toolbar.$element.addClass( 'form-instance-toolbar' );

		this.getButton( 'submit', {
			flags: [ 'progressive', 'primary' ],
			framed: true,
			title: this.buttons.hasOwnProperty( 'submit' ) ?
				this.buttons.submit.label :
				this.enableProgressSave ?
					mw.message( 'mwstake-formengine-inclusion-form-submit-ps-enabled-label' ).text() :
					mw.message( 'mwstake-formengine-form-submit-label' ).text(),
			showBothIconAndLabel: true,
			id: 'formSubmit'
		}, toolFactory );

		if ( this.enableProgressSave ) {
			this.getButton( 'progressSave', {
				flags: [ 'progressive', 'primary' ],
				framed: true,
				title: mw.message( 'mwstake-formengine-form-save-label' ).text(),
				showBothIconAndLabel: true
			}, toolFactory );
		}
		this.getButton( 'reset', {
			title: this.buttons.hasOwnProperty( 'reset' ) ?
				this.buttons.reset.label : mw.message( 'mwstake-formengine-form-reset-label' ).text(),
			showBothIconAndLabel: true
		}, toolFactory );
		this.getButton( 'cancel', {
			title: this.buttons.hasOwnProperty( 'cancel' ) ?
				this.buttons.cancel.label : mw.message( 'mwstake-formengine-form-cancel-label' ).text(),
			showBothIconAndLabel: true
		}, toolFactory );

		let includes = [];
		if ( this.enableProgressSave ) {
			includes.push( 'progressSave' );
		}
		for ( let i = 0; i < this.buttonsToShow.length; i++ ) {
			if ( !this.buttonsToShow[ i ] ) {
				continue;
			}
			if ( this.buttonsToShow[ i ] === 'cancel' && this.canCancel() ) {
				includes.push( 'cancel' );
			} else {
				includes.push( this.buttonsToShow[ i ] );
			}
		}
		includes = includes.reverse();
		toolbar.setup( [
			{
				name: 'actions',
				classes: [ 'actions' ],
				type: 'bar',
				include: includes
			}
		] );
		toolbar.initialize();
		for ( const name in toolbar.tools ) {
			if ( !toolbar.tools.hasOwnProperty( name ) ) {
				continue;
			}
			if ( name === 'submit' ) {
				this.submitButton = toolbar.tools[ name ];
			}
			if ( name === 'progressSave' ) {
				this.progressSaveButton = toolbar.tools[ name ];
			}
			if ( name === 'reset' ) {
				this.resetButton = toolbar.tools[ name ];
			}
			if ( name === 'cancel' ) {
				this.cancelButton = toolbar.tools[ name ];
			}
		}
		if ( includes.length > 0 ) {
			this.$element.append( toolbar.$element );
		}
	};

	OO.initClass( mw.ext.forms.mixin.ButtonsToolbar );

	mw.ext.forms.mixin.ButtonsToolbar.prototype.getButton = function ( name, config, toolFactory ) {
		const form = this,
			button = function () {
				button.parent.apply( this, arguments );
			};
		OO.inheritClass( button, OO.ui.Tool );
		button.static.name = name;
		button.static.icon = config.icon || '';
		button.static.title = config.title || '';
		button.static.flags = config.flags || [];
		button.static.framed = config.framed || false;
		button.prototype.onSelect = function () {
			this.setActive( false );
			form.emit( 'buttonClick', name );
		};
		button.prototype.onUpdateState = function () {};
		toolFactory.register( button );
	};

}( mediaWiki ) );
