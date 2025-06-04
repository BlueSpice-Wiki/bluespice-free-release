# Implement a channel

## Create channel

Create a class that implements `IChannel` for passive channels, that wait on a client to pull its notifications.
Create a class that implements `IPushChannel` for channels that push their notifications to the client (email, chat, etc).

## Register channel

Add the channel to the `NotifyMeChannels` attribute.

## Using the channel

### Channel can provide a configuration, and UI to configure it.

Use `getDefaultConfiguration()` to provide a default configuration.

Implement a JS class that extends `ext.notifyme.ui.ChannelPreferences` and register it to `ext.notifyme.channelConfigurationRegistry` to provide UI

### Should channel skip notifications?

Method `shouldSkip( INotificationEvent $event, UserIdentity $user )` can be used to skip notifications for a specific user.
This is useful in cases when channel is disabled for particular user by configuration.

This should NOT be used to check for subscriptions!

Notifications skipped by this method will not be stored in the database.

### Channels that support pushing - delivery

If channel supports pushing, its `deliver()` method will be called when a notification is ready to be delivered.

If channel is not ready to deliver the notification, it should return `false` and the notification will be queued for later delivery.
It will not fail the notification, just keep it open for delivery.

Returning `true` will mark notification as successfully delivered.

In case channel attempted the delivery, but there is a problem (eg. connection to external service failed), it should throw an exception. This will properly mark notification as failed, and will store the failure reason.


