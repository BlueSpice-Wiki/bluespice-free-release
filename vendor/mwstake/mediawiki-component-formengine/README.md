## MediaWiki Stakeholders Group - Components
# FormEngine for MediaWiki

Provides a OOJS based form engine for MediaWiki.

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Use in a MediaWiki extension

Add `"mwstake/mediawiki-component-formengine": "~2.0"` to the `require` section of your `composer.json` file.

Since 2.0 explicit initialization is required. This can be achived by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).

## Available ResourceLoader modules
- `ext.forms.init`
- `ext.forms.define`
- `ext.forms.standalone`
- `ext.forms.widgets`
- `ext.forms.form.less`

# Requiring additional RL modules
Forms can sometimes use fields that are not loaded in the form package.
To include those packages specify them in the definition

```js
{
	"name": "MyForm",
	"rlDependencies": [ "my.module" ],
	"items": {...},
	...
}
```

# Inline validation
Validate functions can be declared on the widget definition, by using the `validate` key.
This function is tricky as its called also from the context of the input, so no access to the 
form object is possible. If you need to use other elements from the form, use this syntax

```js
{
	name: 'field1',
	label: 'My field',
	type: 'text',
	validate: function( val ) {
		var form = this;
		if ( typeof this.getForm === 'function' ) {
			form = this.getForm();
		}
		if ( !( form instanceof mw.ext.forms.widget.Form ) ) {
			// No form context, we can return true here, as main validation on submit will kick in
			return true;
		}
		// Return true/false...
		// Or return a promise
	}
}
```
