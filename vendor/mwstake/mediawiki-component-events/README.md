# Events

This component allows you to emit notification events to consumers

## Compatibility
- `4.0.x` -> MediaWiki 1.43
- `3.0.x` -> MediaWiki 1.39
- `1.0.x` -> MediaWiki 1.35

## Use in a MediaWiki extension

Require this component in the `composer.json` of your extension:

```json
{
	"require": {
		"mwstake/mediawiki-component-events": "~4"
	}
}
```

## Register consumer

```php
$GLOBALS['wgMWStakeNotificationEventConsumers'][] = [
	'class' => MyConsumer::class,
	'services' => [
		'UserFactory'
	]
];
```

## Create Event

```php
class MyEvent implements \MWStake\MediaWiki\Component\Events\INotificationEvent {
 ....
}

$event = new MyEvent( $user );
```


## Emit Event

```php
$notifier = MediaWikiServices::getInstance()->getService( 'MWStake.Notifier' );
$notifier->emit( $event );

// Will call MyConsumer::consume( $event )
```

