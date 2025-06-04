# UserProfile

## Developer info

### Adding new fields

- Using attribute (static) - `UserProfileFields`

```json
"UserProfileFields": {
    "myfield": {
        "msgKey": "i18n-key-for-label",
        "formDefinition": {
            "type": "text",
            "required": true,
            "placeholder": "placeholder"
            "<<any other values for the Forms field definition>>": true
        },
        "isPublic": true,
        "rlModules": [ "special-rl-module-required-for-form-field" ]
    }   
}
```

- Using config (static) - 'wgUserProfileFields'

```php
$GLOBALS['wgUserProfileFields']['myfield'] = [
    'msgKey' => 'i18n-key-for-label',
    'formDefinition' => [
        'type' => 'text',
        'required' => true,
        'placeholder' => 'placeholder'
        // <<any other values for the Forms field definition>>
    ],
    'isPublic' => true, // If false, shown only to the user
    'rlModules' => [ 'special-rl-module-required-for-form-field' ]
];
```

- Using service (dynamic) - `UserProfile.FieldRegistry`

```php
$registry = MediaWikiServices::getInstance()->getService( 'UserProfile.FieldRegistry' );
$field = new \MediaWiki\Extension\UserProfile\Field\ProfileField( 'myField', 'i18n-key-for-label', true, [
    'type' => 'text',
    'required' => true,
    'placeholder' => 'placeholder'
], [ 'special-rl-module-required-for-form-field' ] );
// Will also override existing field with the same name
$registry->registerField( 'myField', $field );
```

Also, you can unregister a field by calling `$registry->unregisterField( 'myField' )`.

## Logging

Channel `UserProfile.Manager`