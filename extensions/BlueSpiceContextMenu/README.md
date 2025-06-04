# BlueSpiceContextMenu

## Adding items to the context menu
### Server-side
Register a new item in extension attribute `BlueSpiceContextMenuItemFactories` or using the 
`BsContextMenuGetItems` hook. Resulting objects must implement the `BlueSpice\ContextMenu\IMenuItem` interface.

### Client-side
Use the event on `document`

```javascript
$( document ).on( 'BSContextMenuGetItems', function( e, $element, items, forTitle ) {
	var title = new mw.Title( forTitle );
	items.push( {
		id: 'my-item',
		href: title.getUrl( { action: 'fancy-action' } ),
		label: 'MyItem',
		icon: 'oojs-icon',
		primary: false, // if true, will be put to the top of the menu
		overrides: 'some-item', //ID of the item this item should replace
		flags: [ 'progressive' ], // OOJS flags
        callback: function(  ) {
            // do something when button is clicked, do not combine with `href`
            // Called in the context of the context menu
        }
	} );
} );
```