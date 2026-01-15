Config Setting for Language Links - skin.json
=============================================

* "language code": "Sitename with language code placed in respective position".

* '$1' will be replaced by the appropriate title.

## Example:

```json
"config": {
    "DiscoveryHardWiredLangLinks" : {
        "value": {
            "de": "/de/wiki/$1",
            "en": "/wiki/en/$1",
            "fr": "/wiki/$1/fr"
        }
    }
}
```
