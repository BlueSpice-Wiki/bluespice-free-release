## MediaWiki Stakeholders Group - Components
# ManifestRegistry for MediaWiki

Provides a combined registry over all in the `attribute` section registered things in `extension.json` files.

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Compatibility
- `3.0.x` -> MediaWiki 1.43
- `2.0.x` -> MediaWiki 1.39
- `2.0.x` -> MediaWiki 1.35

## Use in a MediaWiki extension

Require this component in the `composer.json` of your extension:

```json
{
	"require": {
		"mwstake/mediawiki-component-manifestregistry": "~3"
	}
}

Since 2.0 explicit initialization is required. This can be achieved by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).

### Register values in extension.json
```JSON
{
	"attributes": {
		"BlueSpiceFoundation": {
			"RoleRegistry": {
				"admin": "\\BlueSpice\\Permission\\Role\\Admin::factory",
				"editor": "\\BlueSpice\\Permission\\Role\\Editor::factory",
				"reader": "\\BlueSpice\\Permission\\Role\\Reader::factory",
				"author": "\\BlueSpice\\Permission\\Role\\Author::factory",
				"reviewer": "\\BlueSpice\\Permission\\Role\\Reviewer::factory",
				"accountmanager": "\\BlueSpice\\Permission\\Role\\AccountManager::factory"
			}
		},
		"BlueSpicePrivacy": {
			"CookieConsentNativeMWCookies": {
				"notificationFlag": {
					"group": "necessary",
					"addPrefix": true
				}
			}
		}
	},
	"manifest_version": 2,
}
```

### Implement in your code
```php
$factory = \MediaWiki\MediaWikiServices::getInstance()->getService( 'MWStakeManifestRegistryFactory' );
$registry = $factory->get( 'MyExtensionMyRegistry' );
$myValues = $registry->getValue( 'subValue' );
$allMyValues = $registry->getAllValues();
```

## Configuration
- `mwsgManifestRegistryOverrides`: Used to overwrite existing registries by either add, remove or merge their values:

*Example 1:*
```php
$GLOBALS['mwsgManifestRegistryOverrides']['MyRegistry'] = [
	'set' => [
		'ReplaceKey' => 'with new value',
	],
	'merge' => [
		'AddThisKey' => 'with this value',
	],
	'remove' => [ 'keyOfValueThatShouldBeRemoved' ]
]
```
