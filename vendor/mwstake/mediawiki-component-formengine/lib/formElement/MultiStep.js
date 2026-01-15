( function ( mw ) {
	mw.ext.forms.formElement.MultiStep = function () {};

	OO.inheritClass( mw.ext.forms.formElement.MultiStep, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.MultiStep.prototype.getElementConfig = function () {
		const config = mw.ext.forms.formElement.MultiStep.parent.prototype.getElementConfigInternal.call( this );
		config.noLayout.value = true;
		return this.returnConfig( config );
	};

	mw.ext.forms.formElement.MultiStep.prototype.getType = function () {
		return 'multi_step';
	};

	mw.ext.forms.formElement.MultiStep.prototype.getWidgets = function () {
		return mw.ext.forms.widget.edit.MultiStep;
	};

	mw.ext.forms.formElement.MultiStep.prototype.getSubitemConfig = function () {
		return {
			propName: 'steps',
			accepts: [ 'multi_step_step' ]
		};
	};

	// DISABLED IN EDITOR - DOESNT WORK
	mw.ext.forms.formElement.MultiStep.prototype.isHidden = function () {
		return true;
	};

	// TODO: doesnt work and also has a dependency to OOJSPlus
	// mw.ext.forms.registry.Type.register( "multi_step", new mw.ext.forms.formElement.MultiStep() );
}( mediaWiki ) );
