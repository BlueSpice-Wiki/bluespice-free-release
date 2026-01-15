# Registering 3rd party form elements

Your extension might want to add a new form element.

To do this, create a formelement by extending `mw.ext.forms.formElement.FormElement` class,
and register it to `mw.ext.forms.registry.Type` as such:

	mw.ext.forms.registry.Type.register( "myElement", new myElement() );

You also need to register the RL module that loads this class:

	$GLOBALS['mwsgFormEngineElementModules'][] = 'ext.myModule';

Your module must depend on `ext.forms.formelements.bootstrap` module.

If your extension does not alrady do it, make sure to init the formengine component BEFORE registering your module
by calling `mwsInitComponents();`
