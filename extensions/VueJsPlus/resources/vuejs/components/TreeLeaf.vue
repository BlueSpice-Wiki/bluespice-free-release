<template>
	<li v-if="selectableLinkNode"
		role="treeitem"
		class="vuejsplus-data-tree-item">
		<div>
			<input
				type="checkbox"
				class="vuejsplus-data-tree-item-checkbox"
				:aria-labelledby="itemId"
				:name="name"
				:value="isSelected"
				:checked="isSelected"
				@change="toggleCheckbox"
			><a :id="itemId" :href="href" :class="nodeClass">{{ label }}</a>
		</div>
	</li>
	<li v-else-if="selectableTextNode"
		role="treeitem"
		class="vuejsplus-data-tree-item">
		<div>
			<input
				type="checkbox"
				class="vuejsplus-data-tree-item-checkbox"
				:aria-labelledby="itemId"
				:name="name"
				:value="isSelected"
				:checked="isSelected"
				@change="toggleCheckbox"
			><span :id="itemId" :class="nodeClass">{{ label }}</span>
		</div>
	</li>
	<li v-else-if="linkNode"
		role="treeitem"
		class="vuejsplus-data-tree-item">
		<div><a :href="href">{{ label }}</a></div>
	</li>
	<li v-else
		role="treeitem"
		class="vuejsplus-data-tree-item">
		<div><span :id="itemId" :class="nodeClass">{{ label }}</span></div>
	</li>
</template>

<script>

// @vue/component
module.exports = exports = {
	name: 'TreeLeaf',
	components: {
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

		return {
			label: this.item.label,
			name: this.item.name,
			itemId: this.item.id,
			nodeClass: nodeClass,
			href: href,
			isSelected: this.selected,
			textNode: textNode,
			linkNode: linkNode,
			selectableTextNode: selectableTextNode,
			selectableLinkNode: selectableLinkNode
		};
	},
	methods: {
		toggleCheckbox: function ( event ) {
			const cb = $( event.target );
			let isSelected = false;
			if ( $( cb ).prop( 'checked' ) === true ) {
				isSelected = true;
			}

			this.item.selected = isSelected;
			this.isSelected = isSelected; // required to update the view

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
