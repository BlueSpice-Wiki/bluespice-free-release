# Implementing new menu types

To implement a new menu you need to:

- Create a menu PHP class that implements `MediaWiki\Extension\MenuEditor\IMenu`.
- Implement a JS class that extends `ext.menueditor.ui.data.tree.Tree` and adapt according to your needs.
- Register menu in attribute `MenuEditorMenus`, in the format `menu-key`: `object spec to instantiate PHP class implemented above`
Make sure that key in the registry matches the one returned by your class's `getKey()` method.

All menu classes are instantiated on `BeforeInitialize` hook. Menu itself is then responsible for showing itself
in appropriate place

# Implementing new nodes



## Node processor
For the node processing, we are using a component `mediawiki-component-wikitext`. Any new node must be registered
with this component before registering it with the MenuEditor extension.

This class is responsible for detecting the node in the source text, modifying them and converting them back to text.

Implement a class that implements `IMenuNodeProcessor` from the component and register using

	$GLOBALS['mwsgWikitextNodeProcessorRegistry']['myprocessor] = $objectSpec

This node processor must reteeurn the actual node (instance of `IMenuNode` from the component) in it's `getNode` method

## Client-side implementation
Once the node is implemented and registered with the component, we can turn to UI implementation.
Register the node class in `MenuEditorNodes` attribute, in format `key` (same as the key in the `IMenuNode` class), with value

	{
		"classname": "JS.class.of.the.node",
		"module": "RL.module.that.loads.the.class"
	}

This JS class must extend `ext.menueditor.ui.data.node.TreeNode`

## Registering new nodes with existing menus
TBD
