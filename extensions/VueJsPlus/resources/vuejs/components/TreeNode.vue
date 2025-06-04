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
				:value="isSelected"
				:checked="isSelected"
				class="vuejsplus-data-tree-item-checkbox"
				@change="toggleCheckbox"
			><a :id="itemId" :href="href" :class="nodeClass">{{ label }}</a>
		</div>
		<ul
			:id="treeId"
			:class="treeClass"
			role="tree"
			:aria-labelledby="itemId">
			<component
				:is="item.type"
				v-for="( item, index ) in children"
				:key="index"
				:item="item"
				:selectable="isSelectable"
				:selected="item.selected"
				@update:model-value="updateModelValue"
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
				:value="isSelected"
				:checked="isSelected"
				class="vuejsplus-data-tree-item-checkbox"
				@change="toggleCheckbox"
			><span :id="itemId" :class="nodeClass">{{ label }}</span>
		</div>
		<ul
			:id="treeId"
			:class="treeClass"
			role="tree"
			:aria-labelledby="itemId">
			<component
				:is="item.type"
				v-for="( item, index ) in children"
				:key="index"
				:item="item"
				:selectable="isSelectable"
				:selected="item.selected"
				@update:model-value="updateModelValue"
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
				:is="item.type"
				v-for="( item, index ) in children"
				:key="index"
				:item="item"
				:selectable="isSelectable"
				:selected="item.selected"
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
				:is="item.type"
				v-for="( item, index ) in children"
				:key="index"
				:item="item"
				:selectable="isSelectable"
				:selected="item.selected"
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
			type: Array
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
	methods: {
		updateModelValue: function ( data ) {
			if ( data.selected === true ) {
				this.$emit(
					'update:model-value',
					data
				);
			}
		},
		toggleCheckbox: function ( event ) {
			const cb = $( event.target );
			const select = false;
			if ( $( cb ).prop( 'checked' ) === true ) {
				isSelected = true;
			}

			for ( let index = 0; index < this.item.children.length; index++ ) {
				this.item.children[ index ].selected = isSelected;
				this.update++; // required to update children
			}

			this.item.selected = isSelected;
			this.isSelected = isSelected; // required to update children view

			this.$emit(
				'update:model-value',
				{
					selected: isSelected,
					name: this.item.name,
					path: this.item.path
				}
			);
		}
	}
};

</script>

<style lang="css">
</style>
