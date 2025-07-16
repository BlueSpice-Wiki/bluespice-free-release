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
				v-model="internalSelected"
				@change="handleCheckboxChange"
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
				v-model="internalSelected"
				@change="handleCheckboxChange"
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

		return {
			label: this.item.label,
			name: this.item.name,
			itemId: this.item.id,
			nodeClass: nodeClass,
			href: href,
			internalSelected: this.selected,
			isSelected: this.selected,
			textNode: textNode,
			linkNode: linkNode,
			selectableTextNode: selectableTextNode,
			selectableLinkNode: selectableLinkNode
		};
	},
	watch: {
		selected: {
			immediate: true,
			handler( newSelected ) {
				this.internalSelected = newSelected;
			}
		},
		internalSelected( newSelected ) {
			this.item.selected = newSelected;
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
		handleCheckboxChange: function () {
		}
	}
};

</script>

<style lang="css">
</style>
