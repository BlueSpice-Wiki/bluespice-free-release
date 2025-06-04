# Where do Droplets come from?

Droplets are provided by `IDropletSource`s. There are currently three droplet sources:

## Attribute registry
You can register a droplet using `ContentDroplets/Droplets` attribute. This holds a list
of key => value pairs, where key is a unique droplet key, and value is an ObjectFactory spec

## GlobalVariable
You can use a global variable droplet registry in the same ways as the attribute registry.
Variable is `$wgContentDropletsDroplets` and values are same key/value pairs as for attribute.

## Wikipage
Droplets can also be registered using the on-wiki specification. Specify droplets on `MediaWiki:ContentDroplets.json` page.

This page holds JSON definition of droplets, eg for templates and tags:

	{
		"myTemplateDroplet": {
			"name": "TemplateDroplet",
			"type": "template",
			"template": "Lorem",
			"params": [
				{ "color": "blue" },
				{ "count": 5 }
			],
			"description": "message-key",
			"icon": "check",
			"rlModules": [],
			"categories": [
				"content"
			],
			"content": "{{Lorem|color="blue"|count=5}}"
		},
		"myTagDroplet": {
			"name": "TagDroplet",
			"type": "tag",
			"tagname": "Lorem",
			"attributes": [
				{ "color": "blue" }
			],
			"hasContent": false,
			"veCommand": "droplet",
			"description": "message-key",
			"icon": "check",
			"rlModules": [],
			"categories": [
				"content"
			],
			"content": "<myDroplet color="blue"/>"
		}
	}

## Priority

Duplicate droplet keys will be override in the order described above, global variable overrides attribute registry,
and wikipage overrides global page.

# Implementing a droplet

Droplet implementation is done on the server side, with additional flexibility possible by implementing a client-side class.

## Server-side
After registering your droplet in one of the three ways described above, you need to implement the droplet class.
This class must implement `MediaWiki\Extension\ContentDroplets\IDropletDescription` interface, but you can use one of the
available base classes for different types of droplets.

Just by implementing the server-side class, Droplet will be instantiated as instance of `ext.contentdroplets.object.Droplet`
on the client-side, and will be displayed in the list.


For Droplets that already have their inspectors and are already handled by VE in some way,
either though "InsertMagic" or default MW stuff like "gallery", do not need any client-side implementation
In such cases server-side class must specify VE command to be used to insert the content

However, for special-needs droplets, a client-side class might be required.

## Client-side
To extend behaviour of your droplet, you should implement a client-side class. This is required mostly for droplets that
do not have a pre-defined inspector (like MW built-in tags, BS tags...) and the droplet itself must provide an inspector.

Implement a class that inherits from `ext.contentdroplets.object.Droplet` or one of the base classes.

To connect server-side and client-side implementations, you must declare a RL module that will load droplet JS class. This
file should also register the droplet with `ext.contentdroplets.registry` registry.

To load this module, return it in `getRLModule()` function of the server-side class.
