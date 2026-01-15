## MediaWiki Stakeholders Group - Components
# CommonUserInterface for MediaWiki

Provides common user interface elements and customizeable stylings.

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Compatibility
- `6.0.x` -> MediaWiki 1.43
- `5.0.x` -> MediaWiki 1.39
- `3.0.x` -> MediaWiki 1.35

## Use in a MediaWiki extension

Require this component in the `composer.json` of your extension:

```json
{
	"require": {
		"mwstake/mediawiki-component-commonuserinterface": "~6"
	}
}
```

Since 3.0 explicit initialization is required. This can be achived by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).

## Components and Renderers

Default renderer types:
- `*`
- `bootstrap-5`

A skin may define

```php
$GLOBALS['mwsgCommonUIComponentRendererType'] = 'bootstrap-5';
```
if it provides "Twitter Bootstrap V5". The "CommonUserInterface" will fall back to `*` of the requested renderer is not available to the chosen type.


### Custom renderers
```php
$GLOBALS['mwsgCommonUIComponentRendererRegistry']['my-custom-renderer']['button'] = '...';
```

## Skin Slots

Default slots:
- `siteNoticeAfter`: Uses hook `SiteNoticeAfter` to add components to any default skin
- `dataAfterContent`: Uses hook `SkinAfterContent` to add components to any default skin

Examples:

```php
$GLOBALS['mwsgCommonUISkinSlots']['siteNoticeAfter']['my-sitenoticeafter-toolbar'] = [
	'factory' => function() {
		return new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleToolbar( [
			'items' => [
				new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropDown( [
					'label' => new RawMessage( 'Click me!' ),
					'toggleRLModules' => [ 'my-dropdown-module' ]
				] )
			]
		] );
	}
];

$GLOBALS['mwsgCommonUISkinSlots']['skinAfterContent']['my-skinaftercontent-panel'] = [
	'factory' => function() {
		return new MWStake\MediaWiki\Component\CommonUserInterface\Component\SimplePanel( [
			'title' => new RawMessage( 'My panel' ),
			'collapsible' => true,
			'collapsed' => true,
			'expandRLModules' => [ 'my-panel-module' ]
		] );
	}
];
```

# TODO

### Client side rendering

Example:
```javascript
mws.commonui.renderPath( '<skinslot>/<parentComponentId>/<subComponentId>', $container );
```
