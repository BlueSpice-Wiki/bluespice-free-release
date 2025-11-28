<template>
	<ul class="opt-list" @focusout="doEmitFocusOut" @keyup.esc="doEmitEsc">
			<component v-for="( option, index ) in options" :key="index"
				:is="option.name"
				v-bind:data-index="dataIndex"
				v-bind:data-type="dataType"
				v-bind:option="option"
				@optionclick="doEmitClick"
				@optionfilter="doEmitFilter"
			></component>
	</ul>
</template>

<script>

var SortASC = require( './SortASC.vue' );
var SortDESC = require( './SortDESC.vue' );
var StringFilter = require( './StringFilter.vue' );
var DateFilter = require( './DateFilter.vue' );

// @vue/component
module.exports = exports = {
	name: 'GridColOptions',
	props: {
		options: {
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
		'SortASC': SortASC,
		'SortDESC': SortDESC,
		'StringFilter': StringFilter,
		'DateFilter': DateFilter,
	},
	emits: [
		'update:model-value',
		'toggle-options-menu'
	],
	data: function () {
		var localOptions = this.options;

		return {
			options: localOptions,
			dataIndex: this.dataIndex,
			dataType: this.dataType
		};
	},
	methods: {
		doEmitClick: function( option ) {
 			this.$emit( 'update:model-value', option );
  		},
		doEmitFilter: function( option ) {
   			this.$emit( 'update:model-value', option );
  		},
		doEmitFocusOut: function( event ) {
			let relatedTarget = event.relatedTarget;
			if ( $( relatedTarget ).hasClass( 'opt-menu' ) ) {
				this.$emit( 'toggle-options-menu' );
				$( event.target ).parents( 'th.opt-menu' ).first().focus();
			}
  		},
		doEmitEsc: function( event ) {
			$( event.target ).parents( 'th.opt-menu' ).first().focus()
			this.$emit( 'toggle-options-menu' );
  		},
	}
};

</script>

<style lang="css">
.opt-list {
	position: absolute;
	left: auto;
	right: 0;
	transform: translateX( 95% );
	width: max-content;
	background-color: #fff;
	border: 1px solid #000;
	border-radius: 5px;
	/* stylelint-disable-next-line declaration-no-important */
	padding: 10px !important;
	list-style: none;
	display: none;
}

.opt-list.show {
	display: block;
	z-index: 5000;
}

.opt-list > li {
	padding: 5px 0;
}

.opt-list > li > button {
	width: 100%;
	background-color: #fff;
	/* stylelint-disable-next-line declaration-property-value-disallowed-list */
	border: none;
	text-align: left;
}

</style>