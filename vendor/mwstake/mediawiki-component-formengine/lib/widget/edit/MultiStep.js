( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.MultiStep = function( cfg ) {
		mw.ext.forms.widget.edit.MultiStep.parent.call( this, cfg );

		this.form = cfg.form;
		this.steps = cfg.steps || [];

		this.configuration = cfg.configuration || 'horizontal';

		this.$input.remove();
		this.$element.addClass( 'ext-forms-widget-multi-step' );
		this.renderItems();
		this.setValue();
		// Disable global form validation, as we only validate page (step) at a time
		this.form.setSkipValidation( true );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.MultiStep, OO.ui.InputWidget );

	mw.ext.forms.widget.edit.MultiStep.prototype.renderItems = function() {
		this.renderStepNavigator();
		this.renderSteps();
		this.renderFooter();

		// The progress bar will set some step by default, set the corresponding page
		this.setStep( this.stepPb.getCurrent() );
		if ( this.form.action === 'view' ) {
			this.$footer.css( 'display', 'none' );
		}
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.renderStepNavigator = function() {
		var buildPbConfig = function( steps ) {
				var config = {};
				for ( var i = 0; i < steps.length; i++ ) {
					var step = steps[i];
					config[step.name] = {
						label: step.label,
						completionStep: step.completionStep
					};
				}
				return config;
			},
			pbConfig = buildPbConfig( this.steps );

		this.stepPb = new OOJSPlus.ui.widget.StepProgressBar( {
			steps: pbConfig
		} );
		this.stepPb.on( 'stepSet', this.stepSet.bind( this ) );
		this.$element.append( this.stepPb.$element );
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.renderSteps = function() {
		var stepName, stepConfig, items = [];
		this.$stepBody = $( '<div>' ).addClass( 'step-body' );
		this.$element.append( this.$stepBody );
		this.stepLayouts = {};
		this.stepItems =  {};

		for( var i = 0; i < this.steps.length; i++ ) {
			stepName = this.steps[i].name;
			stepConfig = this.steps[i];

			this.stepLayouts[stepName] = new OO.ui.FieldsetLayout( {
				classes: [ 'step-body-step step-hidden' ],
				data: {
					stepName: stepName
				}
			} );

			items = stepConfig.items || stepConfig.widget_items;
			this.stepItems[stepName] = this.form.parseItems( items, this.stepLayouts[stepName] );
			this.$stepBody.append( this.stepLayouts[stepName].$element );
		}
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.renderFooter = function() {
		this.$footer = $( '<div>' ).addClass( 'step-footer' );
		this.nextStepButton = new OO.ui.ButtonWidget( {
			label: '',
			flags: [
				'primary',
				'progressive'
			]
		} );
		this.nextStepButton.connect( this, {
			click: 'nextStep'
		} );
		this.$footer.append( this.nextStepButton.$element );
		this.$element.append( this.$footer );
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.nextStep = function() {
		this.validateStep().done( function() {
			this.stepPb.nextStep();
			this.setStep( this.stepPb.getCurrent() );
		}.bind( this ) );
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.validateStep = function() {
		var dfd = $.Deferred(), inputs = {}, inputName;
		if ( !this.stepItems.hasOwnProperty( this.currentStep ) ) {
			dfd.reject();
		}
		for ( inputName in this.stepItems[this.currentStep] ) {
			if ( this.stepItems[this.currentStep][inputName] instanceof OO.ui.InputWidget ) {
				inputs[inputName] = this.stepItems[this.currentStep][inputName];
			}
		}
		if ( $.isEmptyObject( inputs ) ) {
			dfd.resolve();
		}

		this.form.validateForm( inputs ).done( function() {
			dfd.resolve();
		} ).fail( function() {
			dfd.reject();
		} );
		return dfd.promise();
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.stepSet = function( step ) {
		this.setStep( step );
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.setStep = function( step ) {
		var stepName;
		for ( var i = 0; i < this.steps.length; i++ ) {
			stepName = this.steps[i].name;
			this.stepLayouts[stepName].$element.addClass( 'step-hidden' );

			if ( stepName === step ) {
				this.stepLayouts[stepName].$element.removeClass( 'step-hidden' );
				this.currentStep = stepName;
				if ( !this.stepPb.hasMoreSteps() ) {
					this.nextStepButton.setDisabled( true );
					this.nextStepButton.$element.css( 'display', 'none' );
				} else {
					this.nextStepButton.$element.css( 'display', 'inline-block' );
					this.nextStepButton.setDisabled( false );
					this.nextStepButton.setLabel( this.steps[i].nextButtonLabel || mw.message( 'mwstake-formengine-label-next-step' ).text() );
				}
			}
		}
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.setValue = function( value ) {
		if ( !value ) {
			return;
		}
		if ( this.steps.length === 0 ) {
			return;
		}

		if ( value && this.stepPb.hasStep( value.current ) ) {
			this.stepPb.setMax( value.max );
			this.stepPb.setStep( value.current );
			this.setStep( value.current );
			return;
		}
		this.setStep( this.stepPb.getCurrent() );
	};

	mw.ext.forms.widget.edit.MultiStep.prototype.getValue = function() {
		return {
			current: this.stepPb.getCurrent(),
			max: this.stepPb.getMax(),
			completed: this.stepPb.getMax() === 'final' // Final step is always called "final"
		};
	};

} )( mediaWiki, jQuery );