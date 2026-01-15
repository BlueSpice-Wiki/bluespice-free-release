## MediaWiki Stakeholders Group - ComponentLoader
# ComponentLoader for MediaWiki

If a MWStake component needs to access variables from `<mediawiki>/includes/DefaultSettings.php` (e.g. for registering a new "MediaWiki service") this can no longer be done in the regular static file autoloader, provided by Composer (See https://github.com/wikimedia/mediawiki/commit/80fd54ffb028a649e322ad5548832edaa9081954).

Therefore such **components need to require the "componentloader"** and **extensions** using the component **must explicitly invoke the "componentloader"** at `manifest.callback` time!

This can easily be done, either directly within the manifest file (e.g. `extension.json`):

```json
    "callback": "mwsInitComponents"
```

or, in custom code by direct invocation. E.g.

```json
    "callback": "MediaWiki\\Extension\\MyExt\\Setup::callback"
```

```php
<?php

namespace MediaWiki\Extension\MyExt;

class Setup {

	public static function callback() {
		mwsInitComponents();
		// Own code
...

```


