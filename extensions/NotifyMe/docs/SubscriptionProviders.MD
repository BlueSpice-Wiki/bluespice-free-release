# Intro

SubscriptionProviders are responsible for providing users that are subscribed to certain events.

## ManualSubscriberProvider
This provider will select users for given event based on their manual preferences.
Users can add `sets` of events to their preferences, which contain:
- type of set (namespace, category, watchlist, any 3rd party set )
- value of set (which NS, which category...)
- bucket type (content-low-freq, content-high-freq, qa)
- which channel does the set apply to

This means that, for example, user can set to receive events that are in bucket `qa` for all pages in NS_MAIN

All users will be automatically subscribed to all events in `system` and `personal` buckets, as they cannot be opted out of those.

### SubscriptionSets
To define a new subscription set, you need to add it to `ManualSubscriberProviderSets` attribute
in `extension.json`:

```
"attributes": {
	"Notifications": {
		"ManualSubscriberProviderSets": {
			"set_key": {
				// ObjectFactory spec
			}
		}
	}
}
```

Resulting object must implement `ISubscriptionSet` interface

Besides the server side integration, which actually decides whether the set applies to the user and event, you need to add the set to the client side as well.
To do that, first define a RL module that contains all of the necessary files, and return its name in `getClientSideModule` of the
server-side implementation

Client-side implementation consists of two parts:
- set definition
Implement a class that inherits from `ext.notifyme.ui.SubscriptionSet` and register to `ext.notifyme.subscriptionSetRegistry;`
```
ext.notifyme.subscriptionSetRegistry.register( 'my_set', ext.myextension.MySet );
```
- editor
Implement a class that inherits from `ext.notifyme.ui.subscriptionset.editor.SubscriptionSetEditor`
and return an instance of this class in `getEditor` of the set definition class
Note: Not all sets require an editor. In case your set does not require any special configuration, you can just return `null` in `getEditor`

For client-side implementation examples, see RL module `ext.notifyme.subscription.set` which defines the default sets

## External providers
Other than these default providers extensions may decide to provide their own providers, to extend the subscriber set returned by the default providers.
For example, PageAssignments may want to add users that are assigned to a page to the subscriber set on certain events

## Registering providers

Register in `extension.json:`

```
"attributes": {
	"Notifications": {
		"SubscriberProviders": {
			"provider_key": {
				// ObjectFactory spec
			}
		}
	}
}
```

These OF specs must return an object implementing `ISubscriberProvider` interface
