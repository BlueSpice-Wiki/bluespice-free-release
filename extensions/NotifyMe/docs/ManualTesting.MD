## Idea
For debugging, it's very useful to be able to trigger a specific event without needing to actually perform the action
that would normally trigger the event. This is useful for testing and debugging.
That includes being as close as possible to the real event, meaning that it will check for user subscriptions, preferences...

All suitable Events can be triggered (for making Events suitable, see [RegisteringEvents.MD](RegisteringEvents.MD)).

## Script

```bash
php maintenance/triggerEvent.php --key=<event_key> --agent=<agent_username> --page=<page_title>
```

Parameters:
- `--key`: Event key. One of the keys from `NotifyMeEvents` attribute
- `--agent`: Username of the agent that triggered the event. Or `bot` if you want to trigger the event as a bot.
- `--page`: Title of the page that will be used as the subject of the event. Only applies to `TitleEvent` classes.
- `--trigger-all`: Trigger all availble events
- `--target-user`: Some notification only works for specific user. Use this parameter for such notifications
If not specified, and event is a `TitleEvent`, it will use the main page of the wiki.

## Specific notifications caveats

Keep in mind that some notifications only make sense in certain contexts. For example, mention notification only
makes sense if `--target-user` is specified, "edit discussion" notification only makes sense if `--page` is
specified and it is a user discussion page, etc.