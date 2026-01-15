## MediaWiki Stakeholders Group - Components
# Common Web APIs for MediaWiki

Provides various web APIs (Action API and REST).

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Compatibility
- `3.0.x` -> MediaWiki 1.43
- `2.0.x` -> MediaWiki 1.39
- `1.0.x` -> MediaWiki 1.35

## Prerequisites

## Use in a MediaWiki extension

Require this component in the `composer.json` of your extension:

```json
{
	"require": {
		"mwstake/mediawiki-component-commonwebapis": "~2"
	}
}
```

## Getting the available endpoints

```php
$endpoints = MediaWikiServices::getInstance()->getService( 'MWStakeCommonWebAPIs' )->getAvailableEndpoints();
```

will yield a list of all registered endpoints as well as their REST path configuration

## Clientside abstraction

To make it easier to access these endpoints from JS, an abstraction is implemented.

To enable it, load RL module `ext.mws.commonwebapis` and use it like this:

```js
mw.loader.using( 'ext.mws.commonwebapis' ).then( function () {
	mws.commonwebapi.user.query( {
		query: 'MyUser'
	} );
} );
```

## REST API

### Filtering
In order to specify filters, you can use the `filter` parameter. It is an JSON-encoded array of objects. Each object has the following properties:

* `property` - The field to filter on
* `value` - The value to filter on
* `operator` - The operator to use for the filter. Possible values are `eq` (equals), `neq` (not equals), `lt` (less than), `lte` (less than or equal), `gt` (greater than), `gte` (greater than or equal), `like` (like), `nlike` (not like), `in` (in), `nin` (not in), `isnull` (is null), `isnotnull` (is not null), `between` (between), `nbetween` (not between), `ilike` (case insensitive like), `nilike` (case insensitive not like), `regexp` (regular expression), `nregexp` (not regular expression). Depending on the type of the filter, some operators might not be available.
* `type` - The type of the value. Possible values are `string`, `integer`, `float`, `boolean`, `list` 
 
### Sorting
In order to specify sorting, you can use the `sort` parameter. It is an JSON-encoded array of objects. Each object has the following properties:

* `property` - The field to sort on
* `direction` - The direction to sort. Possible values are `asc` (ascending) and `desc` (descending)

### Example

```js
mw.loader.using( 'ext.mws.commonwebapis' ).then( function () {
	mws.commonwebapi.user.query( {
		filter: JSON.stringify( [
			{
				field: 'user_name',
				value: 'MyUser',
				operator: 'eq',
				type: 'string'
			}
		] ),
		sort: JSON.stringify( [
			{
				field: 'user_name',
				direction: 'asc'
			}
		] )
	} );
} );

```
