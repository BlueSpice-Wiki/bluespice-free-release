# VueJsPlus

This extension provides a collection of **Vue 3 components** that can be used in various extensions via the `VueJsPlusModule`.


## **Usage**

All components are located in the folder `vuejs/components`. They are not sorted in any folder to ensure dependencies between components for proper functionality.

### Component Documentation
- All components are documented in the `manifest.json` file.
- Components available for use are marked with the attribute:
  `"isComponent": true`

### Loading Components
To load a component from VueJsPlus in any extension, update the resource loader module in your extension.json file as follows:

```
"myModuleWithVueJsPlusComponent": {
  "class": "MediaWiki\\Extension\\VueJsPlus\\Module\\VueJsPlusModule",
  "vueJsPlusComponents": ["ComponentName1", "ComponentName2"],
  ...
}
```

* `VueJsPlusComponents`: An array where you specify the names of the components to be loaded.

## Usable Components
The following components are currently available:

* Grid
* Toolbar
* Tree

