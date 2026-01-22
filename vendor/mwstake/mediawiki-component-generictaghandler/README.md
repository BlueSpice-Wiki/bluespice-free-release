# GenericTagHandler for MediaWiki

## Register tags

## Tag definition

Implement an instance of `MWStake\MediaWiki\Component\GenericTagHandler\ITag` interface and register it:

```php

// By global
$GLOBALS['mwsgGenericTagRegistry'] = [
    'class' => 'MyClass',
    'services' => [ '...' ]
];

// By hook
$wgHooks['MWStakeGenericTagHandlerInitTags'][] = function ( &$tags ) {
    $tags[] = new MyTag(...);
};

// By registry
$registry = \MediaWiki\MediaWikiServices::getInstance()->getService( 'MWStake.GenericTagHandler.TagFactory' );
$registry->register( new MyTag( ... ) );
```

## VisualEditor integration (over VisualEditorPlus)

You can return a `MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification` from your tag implementation
to automatically register a tag in VisualEditor.

### Without a custom form

Pass `null` to `$formSpecification` param to let the default form be used.

### With server-side-defined form

Pass a `MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification` object to `$formSpecification`
param to use a custom form.

```php
$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'text',
				'name' => 'field',
				'label' => 'My field',
			],
			[
				'type' => 'checkbox',
				'name' => 'framed',
				'label' => 'Is framed'
			],
		] );
		
```

### With client-side defined form

You can implement an instance of `mw.ext.forms.standalone.Form` client-side and use it in the `ClientTagSpecification`

For that you would pass a `MWStake\MediaWiki\Component\FormEngine\FormLoaderSpecification` as `$formSpecification` param.

```php
$formSpec = new FormLoaderSpecification( 'my.form.class', [ 'modules.loading.the.form.class' ] );
```

### Register custom VisualEditor definition classes

In very specific cases you may want to override the default VisualEditor tag definition class

Load a JS file using `getResourceLoaderModules` method, that will override the tag class

```js
mw.hook( 'ext.visualEditorPlus.tags.getTagDefinition' ).add( ( definitionData, tagDefinition ) =>  {
	if ( definitionData.tagname !== 'myfancytag' ) {
		return;
    }
	
	// definition data contains the info from `ClientTagSpecification`
	
	tagDefinition = new my.class.inheriting.from.ext.visualEditorPlus.ui.tags.Definition( {
        ... data
    } );
};
```

In this case, you would likely pass `null` to `$formSpecification` param in the PHP class.