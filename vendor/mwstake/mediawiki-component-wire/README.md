# Setup

This component uses `mwstake/mediawiki-component-token-authenticator` for client authentication.
Configure salt to be used to sign tokens issued to the wire service.
Same value must be configured on `webservice-wire` side (`TOKEN_SALT` env var)

    $GLOBALS['mwsgTokenAuthenticatorSalt'] = '<unique string>';

Url of `webservice-wire` service

    $GLOBALS['mwsgWireServiceUrl'] = '';
    $GLOBALS['mwsgWireServiceAllowInsecureSSL'] = false;

API-key used for HTTP communication with `webservice-wire` service.
Same key must be configured on `webservice-wire` side (`API_KEY` env var)

    $GLOBALS['mwsgWireServiceApiKey'] = '';

Note: If using Insecure SSL you need to visit `mwsgWireServiceUrl` in browser and manually "trust" it, so 
that websocket can establish connection

# Usage

## Sending message

### In application

```php
    $channel = ( new \MWStake\MediaWiki\Component\Wire\WireChannelFactory() )->getChannelForPage( $title );
    $channel = ( new \MWStake\MediaWiki\Component\Wire\WireChannelFactory() )->getChannelForUser( $user );
    $channel = ( new \MWStake\MediaWiki\Component\Wire\WireChannelFactory() )->getGlobalChannel();

    $message = new \MWStake\MediaWiki\Component\Wire\WireMessage(
        $channel,
        [ 'arbitrary' => 'payload' ]
    );
    // or, for creating Wire messages from MW Message object, you can use:
    $message = \MWStake\MediaWiki\Component\Wire\WireMessage::newFromLocalizationMessage(
        \MediaWiki\Message\Message::newFromKey( 'my-text', [ 'a', 'b' ] ),
        $channel
    )

    /** @var \MWStake\MediaWiki\Component\Wire\WireMessenger $messenger */
    $messenger = \MediaWiki\MediaWikiServices::getInstance()->getService( 'MWStake.Wire.Messenger' );
    $messenger->send( $message );
```

### Client-side

```js
const channel = mws.wire.getCurrentPageChannel();
const message = new mws.wire.Message( channel, { arbitrary: 'payload' } );
mws.wire.send( message );
``` 

## Listening for messages

### In application

```php
// Global var
$GLOBALS['mwsgWireListeners'][] = [
    'class' => MyListener::class,
    'services' => [ ... ]
];

// Or hook
$GLOBALS['wgHooks']['MWStakeWireListeners'][] = static function( \MWStake\MediaWiki\Component\Wire\WireListenerRegistry $registry ) {
    $registry->register( new MyListener() );
};

// Class...
class MyListener implements \MWStake\MediaWiki\Component\Wire\WireListener {
    public function onWireMessage( \MWStake\MediaWiki\Component\Wire\WireMessage $message ) {
        // Handle message
    }
}

class MyListener implements \MWStake\MediaWiki\Component\Wire\Listener\WireListenerOnPage {
    public function getPageToListenOn() {
        return $title; // Title to listen for messages on, or null for all pages
    }
    public function onWireMessage( \MWStake\MediaWiki\Component\Wire\WireMessage $message ) {
        // Handle message for a page
    }
}
```

### Client-side

```js
mws.wire.listen( mws.wire.getCurrentPageChannel(), function( payload ) {
	mw.notify( mw.msg( payload.mymsgkey ) );
} );
```