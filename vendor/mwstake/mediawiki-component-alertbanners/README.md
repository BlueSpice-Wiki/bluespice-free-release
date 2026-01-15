## MediaWiki Stakeholders Group - Components
# AlertBanners for MediaWiki

Provides an API for showing banners above the content of a page

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Use in a MediaWiki extension

Add `"mwstake/mediawiki-component-alertbanners": "~2.0"` to the `require` section of your `composer.json` file.

Since 2.0 explicit initialization is required. This can be achived by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).

### Implement a provider

Create a class that implements `MWStake\MediaWiki\Component\AlertBanners\IAlertProvider`. For convenience you may want to derive directly from the abstract base class `MWStake\MediaWiki\Component\AlertBanners\AlertProviderBase`

### Register a provider

There are two ways to register a provider:
1. Using the `mwsgAlertBannersProviderRegistry` GlobalVars configuraton
2. Using the hook `MWStakeAlertBannersRegisterProviders`

On both cases a [ObjectFactory specification](https://www.mediawiki.org/wiki/ObjectFactory) must be provided.

*Example 1: GlobalVars*
```php
$GLOBALS['mwsgAlertProviderRegistry']['my-own-provider'] = [
    'class' => '\\MediaWiki\Extension\\MyExt\\MyAlertProvider',
    'services' => 'MainConfig'
];
```
*Example 2: Hookhandler*
```php
$GLOBALS['wgHooks']['MWStakeAlertBannersRegisterProviders'][] = function( &$providers ) {
    $providers["my-own-provider"] = [
        'class' => '\\MediaWiki\Extension\\MyExt\\MyAlertProvider',
        'services' => 'MainConfig'
    ]
}
```

### Use in client code

Load `mwstake.component.alertbanners` ResourceLoader module.

Available methods:
- `mwstake.alerts.add( id: string, $elem: jQuery, type: string )`: Inject a alert box
- `mwstake.alerts.remove( id: string )`: Remove a alert box
