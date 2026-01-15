( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.MultiStepStep = function() {};

	OO.inheritClass( mw.ext.forms.formElement.MultiStepStep, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.MultiStepStep.prototype.getElementConfig = function() {
		var config = mw.ext.forms.formElement.MultiStepStep.parent.prototype.getElementConfigInternal.call( this ),
			newConfig = {
				name: config.name
			};

		newConfig.label = {
			type: 'text',
			name: 'label',
			label: mw.message( 'mwstake-formengine-label-label' ).text(),
			required: true
		};
		newConfig.completionStep = {
			type: 'checkbox',
			name: 'completionStep',
			value: false,
			label: mw.message( 'mwstake-formengine-label-completion-step' ).text(),
			help: mw.message( 'mwstake-formengine-label-completion-step-help' ).text()
		};

		return this.returnConfig( newConfig );
	};

	mw.ext.forms.formElement.MultiStepStep.prototype.getType = function() {
		return "multi_step_step";
	};

	mw.ext.forms.formElement.MultiStepStep.prototype.getSubitemConfig = function() {
		return {
			propName: 'widget_items',
			accepts: [ 'input', 'static' ]
		};
	};

	// DISABLED IN EDITOR - DOESNT WORK
	mw.ext.forms.formElement.MultiStepStep.prototype.isHidden = function() {
		return true;
	};

	mw.ext.forms.formElement.MultiStepStep.prototype.isSubitem = function() {
		return true;
	};

	mw.ext.forms.formElement.MultiStepStep.prototype.getDisplayName = function() {
		return mw.message( 'mwstake-formengine-form-formelement-multi-step-step' ).text();
	};

	mw.ext.forms.registry.Type.register( "multi_step_step", new mw.ext.forms.formElement.MultiStepStep() );
} )( mediaWiki, jQuery );
