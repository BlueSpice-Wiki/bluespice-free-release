
<template>
	<td v-if="isBoolean"
		v-bind:class="cellClass"
		v-bind:data-index="cell.dataIndex"
		v-bind:data-type="cell.type"
	><img v-bind:src="imgSrc" v-bind:alt="imgAlt" /></td>
	<td v-else-if="isSelectable"
		v-bind:class="cellClass"
		v-bind:data-index="cell.dataIndex"
		v-bind:data-type="cell.type"
	><input type="checkbox" v-bind:name="rowIndex" @change="toggleCheckbox" /></td>
	<td v-else-if="isHtml"
		v-bind:class="cell.class"
		v-bind:data-index="cell.dataIndex"
		v-bind:data-type="cell.type"
		v-html="content"
	></td>
	<td v-else
		v-bind:class="cell.class"
		v-bind:data-index="cell.dataIndex"
		v-bind:data-type="cell.type"
	>{{ content }}</td>
</template>

<script>

// @vue/component
module.exports = exports = {
	name: 'GridDataCell',
	props: {
		cell: {
			type: Array,
			default: []
		},
		rowIndex: {
			type: String,
			default: ''
		}
	},
	components: {
	},
	emits: [
		'rowselected'
	],
	data: function () {
		// TODO: Ensure object properties
		let cellClass = [ this.cell.class ];

		let isHtml = false;
		let isBoolean = false;
		let isSelectable = false;
		if ( this.cell.type === 'boolean' ) {
			isBoolean = true;
		} else if ( this.cell.type === 'html' ) {
			isHtml = true;
		} else if ( this.cell.type === 'selectable' ) {
			isSelectable = true;
		}

		// TODO: Ensure image path, use messages
		let imgSrc = '';
		let imgAlt = '';
		let value = 1;
		if ( this.cell.content === true || this.cell.content === 1 ) {
			value = 2;
		}
		if ( this.cell.content === false || this.cell.content === 0 ) {
			value = 0;
		}
		if ( isBoolean && value === 2 ) {
			imgSrc = 'extensions/VueJsPlus/resources/images/Check_Mark_(0DBA2D).svg.png';
			imgAlt = mw.message( 'vuejsplus-data-grid-boolean-cell-image-alt-text-checked' ).toString();
			cellClass.push( 'vuejsplus-data-grid-boolean-cell checked' );
		} else if ( isBoolean && value === 0 ) {
			imgSrc = 'extensions/VueJsPlus/resources/images/Cross_CSS_Red.svg.png';
			imgAlt = mw.message( 'vuejsplus-data-grid-boolean-cell-image-alt-text-unchecked' ).toString();
			cellClass.push( 'vuejsplus-data-grid-boolean-cell unchecked' );
		}  else if ( isBoolean ) {
			imgSrc = 'extensions/VueJsPlus/resources/images/Dash_icon.svg.png';
			imgAlt = mw.message( 'vuejsplus-data-grid-boolean-cell-image-alt-text-neutral' ).toString();
			cellClass.push( 'vuejsplus-data-grid-boolean-cell neutral' );
		}
		
		let content = this.cell.content;
		if ( this.cell.hasOwnProperty( 'renderer' ) ) {
			let renderer = this.cell.renderer;
			content = window[renderer]( content );
		}

		return {
			'cell': this.cell,
			'cellClass': cellClass.join( ' ' ),
			'isBoolean' : isBoolean,
			'isHtml' : isHtml,
			'isSelectable': isSelectable,
			'imgSrc': imgSrc,
			'imgAlt': imgAlt,
			'rowIndex': this.rowIndex,
			'content': content
		};
	},
	methods: {
		toggleCheckbox: function( event ) {
			let cb = $( event.target );
			let isSelected = false;
			if ( $( cb ).prop( 'checked' ) === true ) {
				isSelected = true;
			}

			this.cell.selected = isSelected;

			this.$emit(
				'rowselected',
				{
					selected: isSelected,
					rowIndex: this.rowIndex
				}
			);
		}
	}
};

</script>

<style lang="css">
.vuejsplus-data-grid-boolean-cell {
	text-align: center;
}
.vuejsplus-data-grid-boolean-cell img {
	max-height: 1em;
}
</style>