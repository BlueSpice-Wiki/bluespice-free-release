## MediaWiki Stakeholders Group - Components
# Utils for MediaWiki

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
		"mwstake/mediawiki-component-utils": "~3"
	}
}
```

Since 2.0 explicit initialization is required. This can be achived by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).
