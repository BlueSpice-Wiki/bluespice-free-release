# Switch to Wire implementation

In version 5.2, implementation was changed from BlueSpicePing to Wire component.

Since these work on different principles, operation of this extension has changed.
Ping will poll on regular intervals (in user context), where Wire listens for changes (with no user context).

For this to work we need two events: `edit start` and `edit end`. 
Staring an edit is simple, either page is loaded in edit mode or VE is activated.
However, `edit end` is more tricky. 

To achieve this, following mechanism is used:

In normal operation, when user starts an edit, API call is made, which will update list of editors and emit
a Wire message to update all client.

Once user is in edit mode, we listen for page unload event, which will trigger a call to API to end the edit
(again, update list of editors and emit a Wire message to update all clients).
This is done using `navigator.sendBeacon` method, which ensures that the request is sent even if the page is unloaded,
in all cases but the browser crash.

Since this is not bulletproof, we also have a fallback mechanism.
During editing, wiki sends periodic keep-alive messages to the server. This will, in turn, update timestamp in
`bs_saferedit` table, keeping it fresh.
Periodic background task (same period as keep-alive signal) will check for stale entries in the table
and delete them.
Keep-alive pings will also take care of "session-loss while editing" concerns.