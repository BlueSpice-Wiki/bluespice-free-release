# Intro

Component `mwstake/mediawiki-component-events` will emit any event that implements `INotificationEvent` interface.
However, in order for that event to be considered by this extension and actually be emitted as a Notification, it has
to be registered. This is needed so that extension can keep track of events and their types and also so that it can
provide a way for users to subscribe to certain events.

Register in `extension.json:`

```json
"attributes": {
	"Notifications": {
		"Events": {
			"event_key": {
                "spec": {
                    "class": "My\\Event\\Class", 
                    "services": [ ... ]
                }
				"buckets": ["admin"]
			}
		}
	}
}
```

Register using a hook, called after loading events from attributes, will override any events registered in `extension.json`:

```php
$wgHooks['NotifyMeRegisterEvents'][] = function( &$events ) {
    $events['event_key'] = [
        'spec' => [
            'class' => 'My\\Event\\Class',
            'services' => [ ... ]
        ],
        'buckets' => ['admin']
    ];
};
```

## Notification even classes
Your class must implement `INotificationEvent` interface. For convenience, you can extend
`MMWStake\MediaWiki\Component\Events\NotificationEvent` or `MWStake\MediaWiki\Component\Events\TitleEvent` classes.
These are base classes for NotificationEvents and provide some basic functionality.

**IMPORTANT: Instances of Event classes are serialized for storage. They must be serializable.**

If your class needs services or some non-serializable objects, you must use `NotifyMe.EventFactory` service
to create instances of your event class. This service will take care of complex serialization and deserialization.
(see #Triggering events)

### Preset subscribers and forcing
Some events don't apply to the general public, but only to certain users or groups. For example, only a single user should 
receive a notification when he is mentioned in a comment.

In such cases, return the target user(s) in `getPresetSubscribers` method of the event. Those users will need to be 
subscribed to the event bucket in order to receive the notification.

If you need to forgo this limitation and make sure user receives the notification regardless of his subscription status,
your event must implement `MWStake\MediaWiki\Component\Events\ForcedEvent` interface. This will make sure that the notification is sent to the user, even if
he is not subscribed to the event bucket.

### Notifying agent
If your event should also be delivered to the agent, implement `MWStake\MediaWiki\Component\Events\NotifyAgentEvent` interface.

### Event messages
Whenever an event is being serialized to be pushed out over a channel, Event class will be called to provide the message
and other user-facing data (eg. link). All of these methods will be passed the `IChannel` object, so that different
messages can be used for different channels (but still, try to have the same message for all channels if any possible).

Main message of the event is provided by `getMessage` method. This method should return a Message object that will used
to generate content to the user.
If you use links to pages in your messages, keep in mind that different channels require different link type (external, internal)
For convenience, you can use `getTitleAnchor( $title, $forChannel)` method to generate wikitext syntax for the link.
It will take care of the correct link type for different channels (available only if inheriting from `TitleEvent`).

### Testability
Even though not explicitly required, every NotificationEvent class should be testable.
Extension provides a script that can (should be able to) trigger any event for testing, without needed to actually perform
the action that would normally trigger the event. This is useful for testing and debugging.

To make your class testable in this way, you must implement `getArgsForTesting` method. This method returns an array of params
that will be passed to the ObjectFactory as `args`. Base classes will already take care of the `agent` and `page` params
( see implementation there ), and your class must only amend this array with any additional params that are needed for
testing.

For this to work, `spec` (with `class` key) must be specified in the event registration.

Args will be applied as such:

```json
"my-event": {
    "spec": {
        "class": "My\\Event\\Class",
        "services": [ "SomeService" ]
    },
    "buckets": ["admin"]
}
```

will be become

```json
"my-event": {
    "spec": {
        "class": "My\\Event\\Class",
        "args": [ "returned", "from", "getArgsForTesting" ],
        "services": [ "SomeService" ]
    },
    "buckets": ["admin"]
}
```

### Triggering events

To trigger your event, you need the instance of your Event class which is then passed to the Notifier service.
To get the instance of your event, you can use `NotifyMe.EventFactory` service (in which case, `spec` must be defined
in the event registration) or you can instantiate it yourself.

```php

$eventFactory = $services->getService( 'NotifyMe.EventFactory' );
$event = $eventFactory->create( 'my_event_key', $args );
// OR (not recommended)
$event = new My\Event\Class( $arg1, $args2, ... );

$notifier = $services->getService( 'MWStake.Notifier' );
$notifier->emit( $event );
```



## Buckets
Buckets are basically categories of events. They are used to group events together and also to provide a way for users
to subscribe to certain buckets.

Available buckets are:
- `personal` - events that are targeted to a particular user or a set of users, eg. user rights change, mentions, tasks...
These events must come with a user or users preset in the event object.
- `qa` - events related to quality assurance, eg. new page workflow, page review, page approval...
- `content-low-freq` - events related to content, but not so frequent, eg. new page, new file, page deleted...
- `content-high-freq` - events related to content, but more frequent, eg. page edited, new version of file uploaded ...

### Defining new buckets
You can define new buckets in `extension.json` file. Just add a new key to `Buckets` in `Notifications` attribute.

Keys `label` and `description` are required, `mandatory` is optional (set to `true` if this bucket cannot be opted out of).

```json
"attributes": {
    "Notifications": {
        "Buckets": {
            "my_bucket": {
                "label": "label-message-key",
                "description": "description-message-key",
                "mandatory": true
            }
        }
    }
}
```