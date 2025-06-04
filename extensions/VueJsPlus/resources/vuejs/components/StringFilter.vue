<template>
	<li>
		<cdx-search-input
			:clearable="true"
			:placeholder="label"
			:aria-label="label"
			@input="doEmit"
		></cdx-search-input>
	</li>
</template>

<script>

var { CdxSearchInput } = require( '@wikimedia/codex' );

// @vue/component
module.exports = exports = {
	name: 'GridStringFilter',
	props: {
		option: {
			type: Array,
			default: []
		},
		dataIndex: {
			type: String,
			default: ''
		},
		dataType: {
			type: String,
			default: ''
		}
	},
	components: {
		CdxSearchInput: CdxSearchInput
	},
	emits: [
		'optionfilter'
	],
	data: function () {
			this.option.filter = '';
			this.option.dataIndex = this.dataIndex;
			this.option.type = this.dataType;
		return {
			'label': this.option.label
		};
	},
	methods: {
		doEmit: function( event ) {
			event.preventDefault();
			event.stopPropagation();

			if ( event.inputType === 'insertText' ) {
				this.option.filter = this.option.filter + event.data;
			}
			if ( event.inputType === 'deleteContentBackward' ) {
				if ( this.option.filter !== '' ) {
					this.option.filter = this.option.filter.substring( 0, this.option.filter.length - 1 );
				}
			}
			// For some reason 
			this.$emit( 'optionfilter', this.option );
  		}
	}
};

</script>

<style lang="css">
</style>