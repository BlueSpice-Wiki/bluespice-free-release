<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_FORMENGINE_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_FORMENGINE_VERSION', '3.0.11' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'formengine', static function () {
	$GLOBALS['mwsgFormEngineElementModules'] = [];

	$GLOBALS['wgResourceModules']['ext.forms.init'] = [
		'localBasePath' => __DIR__ . '/lib',
		'scripts' => [ "ext.forms.init.js" ],
		'messages' => [ "mwstake-formengine-session-loss-error" ],
		'dependencies' => [
			"ext.forms.formelements",
			"oojs-ui.styles.icons-content",
			"oojs-ui.styles.icons-moderation",
			"oojs-ui.styles.icons-editing-core",
			"oojs-ui.styles.icons-editing-advanced",
			"oojs-ui.styles.icons-editing-styling",
			"oojs-ui.styles.icons-interactions",
			"oojs-ui.styles.icons-layout",
			"oojs-ui.styles.icons-media",
			"oojs-ui-windows.icons"
		]
	];

	$GLOBALS['wgResourceModules']['ext.forms.define'] = [
		'localBasePath' => __DIR__ . '/lib',
		'scripts' => [ "ext.forms.define.js" ],
		'dependencies' => [
			"oojs-ui"
		]
	];

	$GLOBALS['wgResourceModules']['ext.forms.standalone'] = [
		'localBasePath' => __DIR__ . '/lib',
		'scripts' => [ "standalone/Form.js" ],
		'dependencies' => [
			"ext.forms.formelements"
		]
	];

	$GLOBALS['wgResourceModules']['ext.forms.formelements.bootstrap'] = [
		'localBasePath' => __DIR__ . '/lib',
		'scripts' => [
			"formElement/base/FormElement.js",
			"formElement/base/InputFormElement.js",
			"formElement/base/FormLayoutElement.js",
		],
		'dependencies' => [
			'ext.forms.define'
		]
	];

	$GLOBALS['wgResourceModules']['ext.forms.formelements'] = [
		'localBasePath' => __DIR__ . '/lib',
		'class' => \MWStake\MediaWiki\Component\FormEngine\FormElementModule::class
	];

	$GLOBALS['wgResourceModules']['ext.forms.widgets'] = [
		'localBasePath' => __DIR__ . '/lib',
		'scripts' => [
			"mixin/BookletLayoutParser.js",
			"mixin/IndexLayoutParser.js",
			"mixin/DefinitionParser.js",
			"mixin/FormLoadingAlert.js",
			"mixin/ButtonsToolbar.js",
			"mixin/EditSummary.js",
			"mixin/Message.js",
			"mixin/Autosave.js",
			"widget/edit/CategoryMultiselect.js",
			"widget/edit/RadioSelectInputWidget.js",
			"widget/edit/Checkbox.js",
			"widget/edit/Radio.js",
			"widget/edit/SelectFileInputWidget.js",
			"widget/edit/Password.js",
			"widget/edit/MultiStep.js",
			"widget/edit/ProgressBar.js",
			"widget/edit/Multiplier.js",
			"widget/edit/Listeners.js",
			"widget/edit/CustomFormProps.js",
			"widget/edit/FormImport.js",
			"widget/view/SingleImageView.js",
			"widget/view/BooleanView.js",
			"widget/view/CategoryMultiselectView.js",
			"widget/view/TextView.js",
			"widget/view/WikiText.js",
			"widget/view/OptionView.js",
			"widget/view/CheckboxMultiselectView.js",
			"widget/view/MenuTagMultiselectView.js",
			"widget/view/Multiplier.js",
			"widget/view/PasswordView.js",
			"widget/edit/CheckboxMultiselect.js",
			"widget/view/RadioSelectView.js",
			"widget/view/SectionLabel.js",
			"widget/view/LabelView.js",
			"widget/view/MessageView.js",
			"widget/Form.js",
			"formElement/Text.js",
			"formElement/TextArea.js",
			"formElement/StaticWikiText.js",
			"formElement/WikiText.js",
			"formElement/Text.js",
			"formElement/Button.js",
			"formElement/CategoryMultiselect.js",
			"formElement/Checkbox.js",
			"formElement/Multiplier.js",
			"formElement/Message.js",
			"formElement/FormImport.js",
			"formElement/Listeners.js",
			"formElement/MenuTagMultiselect.js",
			"formElement/CustomFormProps.js",
			"formElement/CheckboxMultiselect.js",
			"formElement/Dropdown.js",
			"formElement/Icon.js",
			"formElement/Indicator.js",
			"formElement/Label.js",
			"formElement/MultiStep.js",
			"formElement/MultiStepStep.js",
			"formElement/Number.js",
			"formElement/ProgressBar.js",
			"formElement/Radio.js",
			"formElement/RadioMultiselect.js",
			"formElement/SectionLabel.js",
			"formElement/SelectFile.js",
			"formElement/layout/HorizontalLayout.js",
			"formElement/layout/BookletLayout.js",
			"formElement/layout/BookletLayoutPage.js",
			"formElement/layout/IndexLayoutTab.js",
			"formElement/layout/IndexLayout.js",
			"formElement/layout/FieldsetLayout.js",
			"formElement/Title.js",
			"formElement/ComboBox.js",
			"formElement/Password.js",
			"formElement/TagMultiselect.js",
			"formElement/Date.js",
			"widget/view/HorizontalRule.js",
			"formElement/HorizontalRule.js",
		],
		'styles' => [ "ext.forms.form.less" ],
		'dependencies' => [
			"ext.forms.define",
			"oojs-ui",
			"mediawiki.widgets.CategoryMultiselectWidget",
			"mediawiki.widgets.DateInputWidget",
			"ext.forms.formelements.bootstrap"
		],
		'messages' => [
			"mwstake-formengine-error-invalid-form",
			"mwstake-formengine-form-loading-label",
			"mwstake-formengine-form-submit-label",
			"mwstake-formengine-form-reset-label",
			"mwstake-formengine-form-cancel-label",
			"mwstake-formengine-message-ack-button-tooltip",
			"mwstake-formengine-form-validation-failed",
			"mwstake-formengine-form-submit-success",
			"mwstake-formengine-api-generic-error",
			"mwstake-formengine-form-form-name-label",
			"mwstake-formengine-edit-summary-placeholder",
			"mwstake-formengine-inclusion-error-form-not-includable",
			"mwstake-formengine-inclusion-form-submit-ps-enabled-label",
			"mwstake-formengine-form-save-label",
			"mwstake-formengine-form-autosave-success",
			"mwstake-formengine-label-next-step",
			"mwstake-formengine-no-default-value",
			"mwstake-formengine-edit-password-strength-requirement-strong",
			"mwstake-formengine-edit-password-strength-requirement-medium",
			"mwstake-formengine-edit-password-label-password",
			"mwstake-formengine-edit-password-label-password-re",
			"mwstake-formengine-edit-password-strength-indicator-stong",
			"mwstake-formengine-edit-password-strength-indicator-medium",
			"mwstake-formengine-edit-password-strength-indicator-weak",
			"mwstake-formengine-edit-password-error-mismatch",
			"mwstake-formengine-type-message"
		]
	];

	$GLOBALS['wgMessagesDirs']['mwstake-component-formengine'] = __DIR__ . '/i18n';
} );
