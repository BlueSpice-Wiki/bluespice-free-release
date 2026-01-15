<template>
	<li
		v-if="selectableLinkNode"
		class="vuejsplus-data-tree-item"
		role="treeitem">
		<div>
			<input
				type="checkbox"
				:aria-labelledby="itemId"
				:name="name"
				v-model="internalSelected"
				class="vuejsplus-data-tree-item-checkbox"
				@change="handleCheckboxChange"
			><a :id="itemId" :href="href" :class="nodeClass">{{ label }}</a>
		</div>
		<ul
			:id="treeId"
			:class="treeClass"
			role="tree"
			:aria-labelledby="itemId">
			<component
				:is="childItem.type"
				v-for="( childItem, childIndex ) in children"
				:key="childIndex"
				:item="childItem"
				:selectable="isSelectable"
				:selected="childItem.selected" @update:model-value="updateModelValue"
			></component>
		</ul>
	</li>
	<li
		v-else-if="selectableTextNode"
		class="vuejsplus-data-tree-item"
		role="treeitem">
		<div>
			<input
				type="checkbox"
				:aria-labelledby="itemId"
				:name="name"
				v-model="internalSelected"
				class="vuejsplus-data-tree-item-checkbox"
				@change="handleCheckboxChange"
			><span :id="itemId" :class="nodeClass">{{ label }}</span>
		</div>
		<ul
			:id="treeId"
			:class="treeClass"
			role="tree"
			:aria-labelledby="itemId">
			<component
				:is="childItem.type"
				v-for="( childItem, childIndex ) in children"
				:key="childIndex"
				:item="childItem"
				:selectable="isSelectable"
				:selected="childItem.selected" @update:model-value="updateModelValue"
			></component>
		</ul>
	</li>
	<li
		v-else-if="linkNode"
		class="vuejsplus-data-tree-item"
		role="treeitem">
		<div>
			<a :id="itemId" :href="href" :class="nodeClass">{{ label }}</a>
		</div>
		<ul
			:id="treeId"
			:class="treeClass"
			role="tree"
			:aria-labelledby="itemId">
			<component
				:is="childItem.type"
				v-for="( childItem, childIndex ) in children"
				:key="childIndex"
				:item="childItem"
				:selectable="isSelectable"
				:selected="childItem.selected"
			></component>
		</ul>
	</li>
	<li
		v-else
		class="vuejsplus-data-tree-item"
		role="treeitem">
		<div>
			<span :id="itemId" :class="nodeClass">{{ label }}</span>
		</div>
		<ul
			:id="treeId"
			:class="treeClass"
			role="tree"
			:aria-labelledby="itemId">
			<component
				:is="childItem.type"
				v-for="( childItem, childIndex ) in children"
				:key="childIndex"
				:item="childItem"
				:selectable="isSelectable"
				:selected="childItem.selected"
			></component>
		</ul>
	</li>
</template>

<script>
const Leaf = require( './TreeLeaf.vue' );

// @vue/component
module.exports = exports = {
	name: 'Node',
	components: {
		Leaf: Leaf
	},
	props: {
		item: {
			type: Object,
			default: () => ( {} )
		},
		selectable: {
			type: Boolean,
			default: false
		},
		selected: {
			type: Boolean,
			default: false
		}
	},
	emits: [
		'update:model-value'
	],
	data: function () {
		let textNode = true;
		let linkNode = false;
		let selectableTextNode = false;
		let selectableLinkNode = false;

		let href = '';
		if ( this.item.hasOwnProperty( 'href' ) ) {
			href = this.item.href;
			textNode = false;
			linkNode = true;
		}

		let nodeClass = '';
		if ( this.item.hasOwnProperty( 'class' ) ) {
			nodeClass = this.item.class;
		}

		if ( this.selectable === true ) {
			if ( linkNode === true ) {
				linkNode = false;
				selectableLinkNode = true;
			} else {
				textNode = false;
				selectableTextNode = true;
			}
		}

		const classes = [];
		classes.push( 'vuejsplus-data-tree' );
		if ( this.selectable === true ) {
			classes.push( 'vuejsplus-data-tree-selectable' );
		}

		for ( let index = 0; index < this.item.children.length; index++ ) {
			this.item.children[ index ].selected = this.selected;
		}

		return {
			label: this.item.label,
			name: this.item.name,
			itemId: this.item.id,
			nodeClass: nodeClass,
			href: href,
			internalSelected: this.selected,
			isSelected: this.selected,
			treeClass: classes.join( ' ' ),
			children: this.item.children,
			textNode: textNode,
			linkNode: linkNode,
			selectableTextNode: selectableTextNode,
			selectableLinkNode: selectableLinkNode,
			isSelectable: this.selectable,
			update: 0
		};
	},
	watch: {
		selected: {
			immediate: true,
			handler( newSelected ) {
				if ( this.internalSelected !== newSelected ) {
					this.internalSelected = newSelected;
					this.setChildrenSelected( this.children, newSelected );
				}
			}
		},
		internalSelected( newSelected ) {
			this.item.selected = newSelected;
			this.setChildrenSelected( this.children, newSelected );

			this.$emit(
				'update:model-value',
				{
					selected: newSelected,
					name: this.item.name,
					path: this.item.path
				}
			);
		}
	},
	methods: {
		updateModelValue: function ( data ) {
			this.$emit(
				'update:model-value',
				data
			);
		},
		handleCheckboxChange: function () {
 		},
		setChildrenSelected( children, selectedStatus ) {
			if ( !children || children.length === 0 ) {
				return;
			}
			children.forEach( child => {
				if ( child.hasOwnProperty( 'selected' ) ) {
					child.selected = selectedStatus;
				}
			} );
		}
	}
};

</script>
