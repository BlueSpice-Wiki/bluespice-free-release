# MediaWiki Stakeholders Group - Components
# InputProcessor for MediaWiki

Provides a simple framework for processing user input, e.g. from tags, parserfunctions, API paramters, etc.

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Compatibility
- `1.0.x` -> MediaWiki 1.43

## Use in a MediaWiki extension

Require this component in the `composer.json` of your extension:

```json
{
	"require": {
		"mwstake/mediawiki-component-inputprocessor": "~1"
	}
}
```

## Usage

Note: See `phpunit` tests for useful examples

### Define processors
Processors are defined as an assoc array where keys match the field names passed as input and values are instances
of MWStake\MediaWiki\Component\InputProcessor\IProcessor interface.

Ways to create processors:

- Directly create an instance of a processor class

```php
$intProcessor = new MWStake\MediaWiki\Component\InputProcessor\IntValue();
$intProcessor
    ->setRequired( true )
    ->setMin( 0 )
    ->setMax( 999 );
    
$processors = [
    'myNumber' => $intProcessor,
    'myNumber2' => $intProcessor
];
```

- Use a factory to create an instance of a processor class. Useful for more complex processors, requiring more ctor params

```php
$factory = MediaWiki\MediaWikiServices::getInstance()->getService( 'MWStake.InputProcessor.Factory' );
// Either pass configuration directly
$titleProcessor = $factory->createWithData( 'title', [ 'required' => true, 'allowedNamespace' => [ NS_MAIN, NS_USER ] ] );
// Or perform configuration on an instance
$titleProcessor = $factory->create( 'title' );
$titleProcessor
    ->setRequired( true )
    ->setAllowedNamespace( [ NS_MAIN, NS_USER ] );

$processors = [
       ...
    'myPage' => $titleProcessor,
    ....
];
```

- Use static configuration

```php

$processors = [
    'myPage' => [
        'type' => 'title',
        'required' => true,
        'allowedNamespaces' => [ NS_MAIN, NS_USER ]
    ],
    'myNumber' => [
        'type' => 'int',
        'required' => true,
        'min' => 0,
        'max' => 999
    ]
]  

```


Full example
```php

$factory = MediaWiki\MediaWikiServices::getInstance()->getService( 'MWStake.InputProcessor.Factory' );
$namespaceListProcessor = $factory->create( 'namespace-list' );
$namespaceListProcessor
    ->setRequired( true )
    ->setListSeparator( '|' );

$intProcessor = $factory->create( 'integer' );
$intProcessor
    ->setRequired( true )
    ->setMin( 0 )
    ->setMax( 999 );
    
$processors = [
    'source-namespaces' => $namespaceListProcessor,
    'count' => $intProcessor,
    'label' => [
        'type' => 'string',
        'required' => false,
    ]
];    

// User provided input
$input = [
	'source-namespaces' => '0|Help|User|Foo',
	'count' => '5',
	'label' => 'My label'
];

// Process input
$runner = MediaWiki\MediaWikiServices::getInstance()->getService( 'MWStake.InputProcessor' );
$status = $runner->process( $processors, $input );
if ( $status->isGood() ) {
    print_r( $status->getValue() );
    /*
	 * [
	 *     'source-namespaces' => [ 0, 12, 2, 1204 ],
	 *     'count' => 5,
	 *     'label' => 'My label'
	 * ]
	 */
} else {
    $errors = $status->getErrors();
    foreach ( $errors as $error ) {
        $msg = \Message::newFromKey( $error['message'] )->params( ...$error['params'] );
        $errorText = $msg->plain();
        // Display error
    }
}
```

### Register new processor types

```php
// Over config var
$GLOBALS['mwsgInputProcessorRegistry']['my-processor'] = {OF_SPEC};

$GLOBALS['wgHooks']['MWStakeInputProcessorRegisterProcessors'][] = function( &$types ) {
    $types['my-processor'] = {OF_SPEC};
};
```
