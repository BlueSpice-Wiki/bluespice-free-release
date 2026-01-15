
<template>
	<th tabindex="0"
		v-bind:class="thClass" 
		v-bind:aria-label="ariaLabel"
		v-bind:aria-controls="menuId"
		aria-haspopup="true"
		aria-expanded="false"
		v-bind:aria-role="ariaRole"
		v-bind:aria-sorted="col.ariaSorted"
		v-bind:data-colIndex="dataIndex"
		@click="toggleOptionsList"
		@keyup.enter="toggleOptionsList"
		@keyup-space="toggleOptionsList"
	>
		<span>{{ label }}</span>
		<col-options
			v-if="hasOptions"
			v-bind:id="menuId"
			v-bind:data-index="dataIndex"
			v-bind:data-type="dataType"
			v-bind:options="options"
			@update:model-value="doUpdateModel"
			@toggle-options-menu="onOptionsFocusout"
		></col-options>
	</th>
</template>

<script>
var ColOptions = require( './GridColOptions.vue' );

// @vue/component
module.exports = exports = {
	name: 'GridColCell',
	props: {
		col: {
			type: Object
		},
	},
	components: {
		'col-options': ColOptions
	},
	emits: [
		'update:model-value'
	],
	data: function () {
		let hasOptions = false;
		if ( this.col.hasOwnProperty( 'options' ) && this.col.options.length > 0 ) {
			hasOptions = true;
		}
		if ( this.col.sortable === false ) {
			hasOptions = false;
		}
		if ( this.col.filterable === false ) {
			hasOptions = false;
		}
		if ( this.dataIndex === '' ) {
			hasOptions = false;
		}

		let ariaLabel = this.col.label;
		let ariaRole = [ 'columnheader' ];
		let thClass = [ 'opt-menu' ];
		if ( hasOptions ) {
			ariaLabel = mw.message( 'vuejsplus-data-grid-option-button-aria-label', this.col.label ).toString()
			ariaRole.push( 'button' );
			thClass.push( 'opt-menu-btn' );
		}
		
		return {
			dataIndex: this.col.dataIndex,
			dataType: this.col.type.toLowerCase(),
			label: this.col.label,
			options: this.col.options,
			menuId: 'opt-menu-'+this.col.dataIndex,
			hasOptions: hasOptions,
			thClass: thClass.join( ' ' ),
			ariaRole: ariaRole.join( ' ' ),
			ariaLabel: ariaLabel
		};
	},
	methods: {
		toggleOptionsList: function( event ) {
			event.stopPropagation();
			var btn = $( event.target );
			if ( $( btn ).parents( '.opt-list' ).length > 0 ) {
				// click on option in options list
				return;
			};
			if ( $( btn ).hasClass( 'opt-menu' ) === false ) {
				btn = $( btn ).parents( '.opt-menu' ).first();
			}

			if ( $( btn ).hasClass( 'opt-menu-btn' ) === false ) {
				return;
			}

			if ( $( btn ).attr( 'aria-expanded' ) === 'true' ) {
				$( btn ).attr( 'aria-expanded', 'false' );
				$( btn ).find( '.opt-list' ).first().removeClass( 'show' );
			} else {
				// close all other menus
				$( '.opt-menu' ).attr( 'aria-expanded', 'false' );
				$( '.opt-list' ).removeClass( 'show' );
				// open new menu
				$( btn ).attr( 'aria-expanded', 'true' );
				$( btn ).find( '.opt-list' ).first().addClass( 'show' );
			}
		},
		doUpdateModel: function( option ) {
     		this.$emit( 'update:model-value', option );
  		},
		onOptionsFocusout: function() {
			var btn =  $( 'th[data-index'+this.col.dataIndex+']' );
			if ( $( btn ).attr( 'aria-expanded' ) === 'true' ) {
				$( btn ).attr( 'aria-expanded', 'false' );
				$( btn ).find( '.opt-list' ).first().removeClass( 'show' );
			} else {
				// close all other menus
				$( '.opt-menu' ).attr( 'aria-expanded', 'false' );
				$( '.opt-list' ).removeClass( 'show' );
				// open new menu
				$( btn ).attr( 'aria-expanded', 'true' );
				$( btn ).find( '.opt-list' ).first().addClass( 'show' );
			}
		}
	}
};

$( document ).click( function( event ) {
	$( '.opt-menu' ).attr( 'aria-expanded', 'false' );
	$( '.opt-list' ).removeClass( 'show' );
} );


</script>

<style lang="css">
</style>