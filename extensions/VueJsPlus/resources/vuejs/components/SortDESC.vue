<template>
	<li>
		<button class="vuejsplus-data-grid-option-sort-desc" @click="doEmit">{{ label }}</button>
	</li>
</template>

<script>

// @vue/component
module.exports = exports = {
	name: 'SortDESC',
	props: {
		option: {
			type: Array
		},
		dataIndex: {
			type: String
		},
		dataType: {
			type: String,
			default: ''
		}
	},
	components: {
	},
	emits: [
		'sortdesc'
	],
	data: function () {
		this.option.dataIndex = this.dataIndex;
		this.option.type = this.dataType;
		return {
			'label': this.option.label
		};
	},
	methods: {
		doEmit: function() {
			this.$emit( 'optionclick', this.option );
  		}
	}
};

</script>

<style lang="css">
.vuejsplus-data-grid-option-sort-desc {
	padding-left: 20px;
	background-image: url( '../../../VueJsPlus/resources/images/Bootstrap_sort-down.svg.png');
	background-repeat: no-repeat;
	background-position-y: 4px;
}
</style>