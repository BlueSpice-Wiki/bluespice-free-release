
<template>
	<tr v-bind:class="rowClass" v-show="isVisible" v-bind:data-rowindex="rowIndex">
		<td is="vue:datacell"
			v-for="(cell, index) in cells"
			:key="index"
			:cell="cell"
			:row-index="rowIndex"
			@rowselected="handleSelect"
		></td>
	</tr>
</template>

<script>
var DataCell = require( './GridDataCell.vue' );

// @vue/component
module.exports = exports = {
	name: 'GridDataRow',
	props: {
		row: {
			type: Array,
			default: []
		}
	},
	components: {
		'datacell': DataCell
	},
	emits: [
	],
	data: function () {
		// TODO: Ensure object properties
		return {
			'rowClass': this.row.class,
			'isVisible': this.row.isVisible,
			'cells': this.row.data,
			'rowIndex': this.row.rowIndex
		};
	},
	methods: {
		handleSelect: function( data ) {
			if ( this.rowIndex === data.rowIndex ) {
				this.row.selected = data.selected;

				if ( data.selected === true ) {
					this.rowClass += ' selected'
				} else {
					this.rowClass = this.row.class;
				}
			}
		}
	}
};

</script>
