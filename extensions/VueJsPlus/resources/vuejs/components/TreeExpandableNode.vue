<template>
	<li
		v-if="selectableLinkNode"
		class="vuejsplus-data-tree-item"
		role="treeitem">
		<div>
			<a
				role="button"
				:class="expandBtnClass"
				:aria-label="expandBtnAriaLabel"
				:aria-expanded="isExpanded"
				:aria-controls="treeId"
				@click="toggleTree"
			></a><div style="display: inline-block; position: relative;">
				<input
					type="checkbox"
					:aria-labelledby="itemId"
					:name="name"
					v-model="internalSelected"
					class="vuejsplus-data-tree-item-checkbox"
					@change="handleCheckboxChange"
				><a :id="itemId" :href="href" :class="nodeClass">{{ label }}</a>
			</div>
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
			<a
				role="button"
				:class="expandBtnClass"
				:aria-label="expandBtnAriaLabel"
				:aria-expanded="isExpanded"
				:aria-controls="treeId"
				@click="toggleTree"
			></a><div style="display: inline-block; position: relative;">
				<input
					type="checkbox"
					:aria-labelledby="itemId"
					:name="name"
					v-model="internalSelected"
					class="vuejsplus-data-tree-item-checkbox"
					@change="handleCheckboxChange"
				><span :id="itemId" :class="nodeClass">{{ label }}</span>
			</div>
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
			<a
				role="button"
				:class="expandBtnClass"
				:aria-label="expandBtnAriaLabel"
				:aria-expanded="isExpanded"
				:aria-controls="treeId"
				@click="toggleTree"
			></a>
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
			<a
				role="button"
				:class="expandBtnClass"
				:aria-label="expandBtnAriaLabel"
				:aria-expanded="isExpanded"
				:aria-controls="treeId"
				@click="toggleTree"
			></a>
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
	name: 'ExpandableNode',
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
		classes.push( 'vuejsplus-data-tree-expandable' );
		if ( this.selectable === true ) {
			classes.push( 'vuejsplus-data-tree-selectable' );
		}

		const expandBtnClasses = [];
		expandBtnClasses.push( 'vuejsplus-data-tree-expand-btn' );
		if ( this.item.expanded === true ) {
			expandBtnClasses.push( 'expanded' );
			classes.push( 'show' );
		}

		let expandBtnAriaLabel = '';
		if ( this.item.expanded ) {
			expandBtnAriaLabel = mw.message( 'vuejsplus-data-tree-expand-btn-expanded-aria-label' ).toString();
		} else {
			expandBtnAriaLabel = mw.message( 'vuejsplus-data-tree-expand-btn-collapsed-aria-label' ).toString();
		}

		for ( let index = 0; index < this.item.children.length; index++ ) {
			this.item.children[ index ].selected = this.selected;
		}

		return {
			label: this.item.label,
			name: this.item.name,
			itemId: this.item.id,
			treeId: this.item.id + '-tree',
			nodeClass: nodeClass,
			internalSelected: this.selected,
			href: href,
			isSelected: this.selected,
			treeClass: classes.join( ' ' ),
			children: this.item.children,
			textNode: textNode,
			linkNode: linkNode,
			selectableTextNode: selectableTextNode,
			selectableLinkNode: selectableLinkNode,
			isSelectable: this.selectable,
			expandBtnClass: expandBtnClasses.join( ' ' ),
			isExpanded: this.item.expanded,
			expandBtnAriaLabel: expandBtnAriaLabel,
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
		},
		toggleTree: function ( event ) {
			const btn = event.target;
			const item = $( btn ).parents( '.vuejsplus-data-tree-item' ).first();

			const isCurrentlyExpanded = $( btn ).attr( 'aria-expanded' ) === 'true';
			const newExpandedStatus = !isCurrentlyExpanded;

			const ariaLabel = newExpandedStatus
				? mw.message( 'vuejsplus-data-tree-expand-btn-expanded-aria-label' ).text()
				: mw.message( 'vuejsplus-data-tree-expand-btn-collapsed-aria-label' ).text();

			$( btn ).attr( 'aria-expanded', newExpandedStatus );
			$( btn ).attr( 'aria-label', ariaLabel );

			if ( newExpandedStatus ) {
				$( btn ).addClass( 'expanded' );
				$( item ).find( '.vuejsplus-data-tree-expandable' ).first().addClass( 'show' );
			} else {
				$( btn ).removeClass( 'expanded' );
				$( item ).find( '.vuejsplus-data-tree-expandable' ).first().removeClass( 'show' );
			}
			this.item.expanded = newExpandedStatus;
			this.isExpanded = newExpandedStatus;
		}
	}
};

</script>

<style lang="css">
.vuejsplus-data-tree-expandable {
	display: none;
}

.vuejsplus-data-tree-expandable.show {
	display: block;
}

.vuejsplus-data-tree-expand-btn {
	display: inline-block;
	width: 30px;
	height: 20px;
	background-image: url( ../../../VueJsPlus/resources/images/OOUI-style-expand-tblr.svg.png );
	background-repeat: no-repeat;
	background-position: 0.25em;
}

.vuejsplus-data-tree-expand-btn.expanded {
	background-image: url( ../../../VueJsPlus/resources/images/20px-OOjs_UI_icon_expand.svg.png );
}
</style>
