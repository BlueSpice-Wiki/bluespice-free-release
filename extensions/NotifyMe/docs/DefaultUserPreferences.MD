# Setting default user subscriptions

User preferences are stored as JSON in the `user_properties` table.

Key is `ext-notification-subscriptions`.

Example value for the preferences:

```json
{
   "channels":{
      "email":{
         "enabled":true,
         "frequency":"instant"
      }
   },
   "subscriptions":[
      {
         "setType":"ns",
         "set":{
            "ns":"14"
         },
         "bucket":"content-high-freq",
         "channels":[
            "web",
            "email"
         ]
      },
      {
         "setType":"ns",
         "set":{
            "ns":"0"
         },
         "bucket":"content-high-freq",
         "channels":[
            "web",
            "email"
         ]
      }
   ]
}
```

Each of the `subscriptions` is a "tile", a subscription set. It has the following fields:

- `setType` - type of the subscription, one of the keys in `NotifyMeManualSubscriberProviderSets` attribute
- `set` - parameters for the subscription, depends on the `setType`
- `bucket` - frequency bucket, one of the keys in `NotifyMeBuckets` attribute
- `channels` - where to deliver notification to

All of the keys are required!

## Settings values

### Passive 

```php
$wgDefaultUserOptions['ext-notification-subscriptions'] = '{"channels":{"email":{"enabled":true,"frequency":"instant"}},"subscriptions":[{"setType":"ns","set":{"ns":"14"},"bucket":"content-high-freq","channels":["web","email"]},{"setType":"ns","set":{"ns":"0"},"bucket":"content-high-freq","channels":["web","email"]}]}';
```

### Active

Even though setting this preference can be done through standard `userOptions.php` script, for convenience,
 because of its complicated value, there is a custom maintenance script that can be used to set the default user subscriptions.

```bash
php extensions/NotifyMe/maintenance/setDefaultUserSubscriptions.php --json='{"channels":{"email":{"enabled":true,"frequency":"instant"}},"subscriptions":[{"setType":"ns","set":{"ns":"14"},"bucket":"content-high-freq","channels":["web","email"]},{"setType":"ns","set":{"ns":"0"},"bucket":"content-high-freq","channels":["web","email"]}]}'
```
or
```bash
php extensions/NotifyMe/maintenance/setDefaultUserSubscriptions.php /path/to/file.json
```

