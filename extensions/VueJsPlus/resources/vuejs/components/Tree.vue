<template>
	<toolbar
		:items="tools"
		@toolclick="onToolbarToolClick"
	></toolbar>
	<div class="vuejsplus-data-tree-cnt">
		<ul :class="cntClass" role="tree">
			<component
				:is="item.type"
				v-for="( item, index ) in items"
				:key="index"
				:item="item"
				:selectable="isSelectable"
				:selected="item.selected"
				@update:model-value="updateModelValue"
			></component>
		</ul>
	</div>
</template>

<script>
const { defineComponent } = require( 'vue' );
const Toolbar = require( './Toolbar.vue' );
const Leaf = require( './TreeLeaf.vue' );
const Node = require( './TreeNode.vue' );
const ExpandableNode = require( './TreeExpandableNode.vue' );

let itemId = 1;

// @vue/component
module.exports = defineComponent( {
	name: 'Tree',
	components: {
		Leaf: Leaf,
		Node: Node,
		ExpandableNode: ExpandableNode,
		Toolbar: Toolbar
	},
	props: {
		class: {
			type: String,
			default: ''
		},
		nodes: {
			type: Array,
			default: []
		},
		selectable: {
			type: Boolean,
			default: false
		},
		selected: {
			type: Boolean,
			default: false
		},
		expandable: {
			type: Boolean,
			default: false
		},
		expanded: {
			type: Boolean,
			default: false
		},
		toolbar: {
			type: Boolean,
			default: false
		},
		tools: {
			type: Array,
			default: []
		},
		toolbarFloatingOffset: {
			type: Number,
			default: 0
		}
	},
	mounted () {
		var $toolbar = $( '.vuejsplus-toolbar' );
		var topValue = $( $toolbar ).offset().top;
		this.topValue = topValue;
		this.contentWidth = $( '#mw-content-text' ).innerWidth();
	},
	created () {
		window.addEventListener( 'scroll', this.handleFloatingToolbar );
	},
	unmounted () {
		window.removeEventListener( 'scroll', this.handleFloatingToolbar );
	},
	data: function () {
		const nodes = this.nodes;
		for ( let index = 0; index < nodes.length; index++ ) {
			prepareNode( nodes[ index ], this.selectable, this.selected, this.expandable, this.expanded );
		}

		const classes = [];
		classes.push( this.class );
		classes.push( 'vuejsplus-data-tree' );
		if ( this.selectable === true ) {
			classes.push( 'vuejsplus-data-tree-selectable' );
		}
		if ( this.expandable === true ) {
			classes.push( 'vuejsplus-data-tree-expandable show' );
		}

		return {
			cntClass: classes.join( ' ' ),
			items: this.nodes,
			isSelectable: this.selectable,
			hasToolbar: this.toolbar,
			tools: this.tools,
			topValue: 0,
			contentWidth: 1200
		};
	},
	methods: {
		updateModelValue: function ( data ) {
		},
		onToolbarToolClick: function ( data ) {
			if ( data.hasOwnProperty( 'callback' ) ) {
				const callback = data.callback;
				window[ callback ]( this.items );
			}
		},
		handleFloatingToolbar: function () {
			var windowTop = $( window ).scrollTop();
			var $toolbar = $( '.vuejsplus-toolbar' );
			if ( windowTop > this.topValue ) {
				if ( !$( $toolbar ).hasClass( 'vuejsplus-toolbar-floating' ) ) {
					$( $toolbar ).addClass( 'vuejsplus-toolbar-floating' );
					$toolbar.css( 'top', this.toolbarFloatingOffset );
					$toolbar.css( 'width', this.contentWidth );
				}
			} else {
				if ( $( $toolbar ).hasClass( 'vuejsplus-toolbar-floating' ) ) {
					$toolbar.removeAttr( 'style' );
					$( $toolbar ).removeClass( 'vuejsplus-toolbar-floating' );
				}
			}
		}
	}
} );

function prepareNode( node, isSelectable, isSelected, isExpandable, isExpanded, path = '' ) {
	const leaf = 'Leaf';
	const tree = 'Node';
	const expandableTree = 'ExpandableNode';
	node.path = path + '/' + node.name;

	if ( !node.hasOwnProperty( 'id' ) ) {
		node.id = 'vuejsplus-tree-item-' + itemId;
		itemId++;
	}

	if ( isExpandable === true && isExpanded === true && !node.hasOwnProperty( 'expanded' ) && node.hasOwnProperty( 'children' ) ) {
		node.expanded = true;
	} else if ( isExpandable === true && !node.hasOwnProperty( 'expanded' ) && node.hasOwnProperty( 'children' ) ) {
		node.expanded = false;
	}

	if ( isSelectable === true && isSelected === true && !node.hasOwnProperty( 'selected' ) ) {
		node.selected = true;
	} else if ( isSelectable === true && !node.hasOwnProperty( 'selected' ) ) {
		node.selected = false;
	}

	if ( !node.hasOwnProperty( 'children' ) || node.children.length === 0 ) {
		node.type = leaf;
		return;
	}
	node.type = tree;

	if ( isExpandable === true ) {
		node.type = expandableTree;
	}

	for ( let idx = 0; idx < node.children.length; idx++ ) {
		prepareNode( node.children[ idx ], isSelectable, isSelected, isExpandable, isExpanded, node.path );
	}
}

</script>

<style lang="css">
.vuejsplus-data-tree-cnt {
	padding-top: 0.5em;
}
ul.vuejsplus-data-tree {
	list-style: none;
	padding-left: 0;
	margin-left: 1em;
	margin-top: 0;
}
ul.vuejsplus-data-tree {
	list-style: none;
	padding-left: 5px;
	margin-left: 1.5em;
}
ul.vuejsplus-data-tree.vuejsplus-data-tree-expandable {
	margin-left: 3.5em;
}
ul.vuejsplus-data-tree > li {
    position: relative;
	margin-bottom: 0;
}
ul.vuejsplus-data-tree > li::before,
ul.vuejsplus-data-tree > li::after {
    content: "";
    position: absolute;
    left: -0.5em;
}
ul.vuejsplus-data-tree > li::before {
    border-top: 1px dotted #000;
    top: 0.75em;
    width: 0.75em;
    height: 0;
}
ul.vuejsplus-data-tree > li::after {
    border-left: 1px dotted #000;
    height: 100%;
    width: 0px;
    top: 4px;
}
ul.vuejsplus-data-tree > li:last-child {
	padding-bottom: 0.5em;
}
ul.vuejsplus-data-tree > li:last-child::after {
    height: 8px;
}
.vuejsplus-data-tree-item > div:not( :has( input ) ) {
	padding-left: 5px;
}
ul.vuejsplus-data-tree.vuejsplus-data-tree-selectable input[type='checkbox'] {
	margin-right: 0.5em;
	cursor: pointer;
	width: 1.5625em;
	height: 1.5625em;
	max-width: none;
	opacity: 0;
	position: relative;
	z-index: 1;
}
.vuejsplus-data-tree-item-checkbox + span::before,
.vuejsplus-data-tree-item-checkbox + a::before {
	cursor: pointer;
	transition: background-color 100ms,color 100ms,border-color 100ms,box-shadow 100ms;
	content: '';
	background-color: #fff;
	background-origin: border-box;
	background-position: center center;
	background-repeat: no-repeat;
	background-size: 0 0;
	box-sizing: border-box;
	position: absolute;
	top: 1em;
	left: 0;
	width: 1.5625em;
	height: 1.5625em;
	margin-top: -0.78125em;
	border: 1px solid #72777d;
	border-radius: 0;
}

.vuejsplus-data-tree-item-checkbox:checked + span::before,
.vuejsplus-data-tree-item-checkbox:checked + a::before {
	background-color: #3366cc;
	border-color: #3366cc;
	background-image: url("../../../../resources/src/mediawiki.ui.checkbox/images/checkbox-checked.svg");
	background-size: 90% 90%;
}
</style>
