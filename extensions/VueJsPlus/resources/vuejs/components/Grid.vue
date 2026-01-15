<template>
	<toolbar v-if="hasToolbar"
		v-bind:items="tools"
		@toolclick="onToolbarToolClick"
	></toolbar>
	<table v-bind:class="tableClass">
		<thead>
			<tr>
				<th is="vue:column"
					v-for="datacol in datacols"
					:key="datacol.dataIndex"
					v-bind:col="datacol"
					@update:model-value="doUpdateModel"
				></th>
			</tr>
		</thead>
		<tbody>
			<tr is="vue:datarow" v-for="datarow in datarows"
				:key="datarow.dataIndex"
				v-show="datarow.isVisible"
				v-bind:row="datarow"
			></tr>
		</tbody>
	</table>
	<div
		v-bind:id="ariaLiveId"
		class="vuejsplus-aria-lve"
		aria-live="polite"
	></div>
</template>

<script>
var Toolbar = require( './Toolbar.vue' );
var ColCell = require( './GridColCell.vue' );
var DataRow = require( './GridDataRow.vue' );

// @vue/component
module.exports = exports = {
	name: 'Grid',
	props: {
		class: {
			type: String,
			default: ''
		},
		cols: {
			type: Array
		},
		rows: {
			type: Array
		},
		toolbar: {
			type: Boolean,
			default: false
		},
		tools: {
			type: Array,
			default: []
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
	components: {
		'column': ColCell,
		'datarow': DataRow,
		'toolbar': Toolbar
	},
	data: function () {
		this.ariaLiveId = 'vuejsplus-data-grid-'+Math.floor( Math.random() * 10000 );
		this.availableDataIndex = [];
		var availableCols = [];
		var availableRows = [];
		var dataIndexColumnTypeMap = [];
		var renderer = [];

		// prepare defaults for sortable and filterable
		prepareColumns( this.cols, this.selectable );
		for ( var index = 0; index < this.cols.length; index++ ) {
			if ( this.cols[index].hasOwnProperty( 'dataIndex' ) ) {
				let dataIndex = this.cols[index].dataIndex;
				this.availableDataIndex.push( dataIndex );
				availableCols.push( this.cols[index] );
				dataIndexColumnTypeMap[dataIndex] = this.cols[index].type;
				if ( this.cols[index].hasOwnProperty( 'renderer' ) ) {
					renderer[dataIndex] = this.cols[index].renderer;
				}
			}
		}

		// prepare rows and row.data
		prepareRows( this.rows, availableRows, dataIndexColumnTypeMap, this.selectable, this.selected, renderer );

		return {
			datacols: availableCols,
			datarows: availableRows,
			tableClass: this.class,
			ariaLiveId: this.ariaLiveId,
			hasToolbar: this.toolbar,
			tools: this.tools,
		};
	},
	methods: {
		doUpdateModel: function( option ) {
			let dataIndex = option.dataIndex;

			if ( option.name === 'SortASC' ) {
				if ( option.type === 'numeric' ) {
					sortNumericASC( this.datarows, dataIndex );
				} else if ( option.type === 'boolean' ) {
					sortTristateASC( this.datarows, dataIndex );
				} else if ( option.type === 'date' ) {
					sortDateASC( this.datarows, dataIndex );
				} else if ( option.type === 'html' ) {
					sortHtmlASC( this.datarows, dataIndex );
				} else if ( option.type === 'string' ) {
					sortStringASC( this.datarows, dataIndex );
				}
				this.datacols.find( obj => { return obj.dataIndex===dataIndex } ).ariaSorted = 'ascending'
			} else if ( option.name === 'SortDESC') {
				if ( option.type === 'numeric' ) {
					sortNumericDESC( this.datarows, dataIndex );
				} else if ( option.type === 'boolean' ) {
					sortTristateDESC( this.datarows, dataIndex );
				} else if ( option.type === 'date' ) {
					sortDateDESC( this.datarows, dataIndex );
				} else if ( option.type === 'html' ) {
					sortHtmlDESC( this.datarows, dataIndex );
				} else if ( option.type === 'string' ) {
					sortStringDESC( this.datarows, dataIndex );
				}
				this.datacols.find( obj => { return obj.dataIndex===dataIndex } ).ariaSorted = 'descending'
			} else if ( option.name === 'StringFilter' && option.type === 'string' ) {
				filterStringSingleColumn( this.datarows, dataIndex, option.filter );
				let visibleRows = this.datarows.filter( obj => { return obj.isVisible===true } );
				text = mw.message( 'vuejsplus-data-grid-aria-live-filtered-rows', visibleRows.length ).toString();
				updateAriaLiveSection( this.ariaLiveId, text );
			} else if ( option.name === 'StringFilter' && option.type === 'html' ) {
				filterHTMLSingleColumn( this.datarows, dataIndex, option.filter );
				let visibleRows = this.datarows.filter( obj => { return obj.isVisible===true } );
				text = mw.message( 'vuejsplus-data-grid-aria-live-filtered-rows', visibleRows.length ).toString();
				updateAriaLiveSection( this.ariaLiveId, text );
			} else if ( option.name === 'DateFilter') {
				filterDateSingleColum( this.datarows, dataIndex, option.filter );
				let visibleRows = this.datarows.filter( obj => { return obj.isVisible===true } );
				text = mw.message( 'vuejsplus-data-grid-aria-live-filtered-rows', visibleRows.length ).toString();
				updateAriaLiveSection( this.ariaLiveId, text );
			}
	
			function updateAriaLiveSection( targetId, text ) {
				$( '#'+targetId ).html( text );
			}

			function sortStringASC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					return ( a === b ) ? 0 : ( a < b ) ? -1 : 1;
				}
			}

			function sortStringDESC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					return ( a === b ) ? 0 : ( a > b ) ? -1 : 1;
				}
			}

			function sortNumericASC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					return ( a - b );
				}
			}

			function sortNumericDESC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					return ( b - a );
				}
			}

			function sortHtmlASC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					var aText = $( '<div>'+a+'</div>' ).text();
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					var bText = $( '<div>'+b+'</div>' ).text();
					return ( aText === bText ) ? 0 : ( aText < bText ) ? -1 : 1;
				}
			}

			function sortHtmlDESC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					var aText = $( '<div>'+a+'</div>' ).text();
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();
					var bText = $( '<div>'+b+'</div>' ).text();
					return ( aText === bText ) ? 0 : ( aText > bText ) ? -1 : 1;
				}
			}

			function sortTristateASC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					if ( a === false || a === 0 ) {
						a = -1;
					} else if ( a === true || a === 1 ) {
						a = 1;
					} else {
						a = 0;
					}
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					if ( b === false || b === 0 ) {
						b = -1;
					} else if ( b === true || b === 1 ) {
						b = 1;
					} else {
						b = 0;
					}
					return ( a > b );
				}
			}

			function sortTristateDESC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					if ( a === false || a === 0 ) {
						a = -1;
					} else if ( a === true || a === 1 ) {
						a = 1;
					} else {
						a = 0;
					}
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					if ( b === false || b === 0 ) {
						b = -1;
					} else if ( b === true || b === 1 ) {
						b = 1;
					} else {
						b = 0;
					}
					return ( a < b );
				}
			}

			function getDateTimestamp( text ) {
				//https://stackoverflow.com/questions/2258293/convert-dd-mm-yyyy-format-to-yyyy-mm-dd
				var isEuro = text.match( /^\d{1,2}\.\d{1,2}\.\d{2,4}$/ );
				var isIso = text.match( /^\d{2,4}-\d{1,2}-\d{1,2}$/ );
				if ( isEuro ) {
					text = text.split('.')
					text = [ text[2], text[1], text[0] ].join( '-' ) //.reverse() isn't working in IEdge
					isIso = true
				}
				if ( isEuro || isIso ) {
					var contentDate = new Date( text );
				}
				return contentDate.getTime();
			}

			function sortDateASC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					let timestampA = getDateTimestamp( a );
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					let timestampB = getDateTimestamp( b );
					return ( timestampA - timestampB );
				}
			}

			function sortDateDESC( rows, dataIndex ){
				rows.sort( sortFunction );
				function sortFunction( a, b ) {
					a = a.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					let timestampA = getDateTimestamp( a );
					b = b.data.find( obj => { return obj.dataIndex===dataIndex } ).content;
					let timestampB = getDateTimestamp( b );
					return ( timestampB - timestampA );
				}
			}

			// filter only on column, other filters are removed
			function filterStringSingleColumn( rows, dataIndex, filterString ) {
				filterString = filterString.toLowerCase();
				if ( filterString === '' ) {
					for ( var index = 0; index < rows.length; index++ ) {
						rows[index].isVisible = true;
					}
				} else {
					for ( var index = 0; index < rows.length; index++ ) {
						var content = rows[index].data.find( obj => { return obj.dataIndex===dataIndex } ).content.toLowerCase();

						if ( content.search( filterString )  === -1 ) {
							rows[index].isVisible = false;
						} else {
							rows[index].isVisible = true;
						}
					}
				}
			}

			function filterHTMLSingleColumn( rows, dataIndex, filterString ) {
				filterString = filterString.toLowerCase();
				if ( filterString === '' ) {
					for ( var index = 0; index < rows.length; index++ ) {
						rows[index].isVisible = true;
					}
				} else {
					for ( var index = 0; index < rows.length; index++ ) {
						let content = rows[index].data.find( obj => { return obj.dataIndex===dataIndex } ).content;
						let html = $( '<div>'+content+'</div>' );
						let text = html.text();

						// Find alt text of images
						let matches = [...content.matchAll( '<img.*?alt="(.*?)".*?>')];
						if ( matches.length > 0 ) {
							for ( var idx = 0; idx < matches.length; idx++ ) {
								text = text+matches[idx][1];
							}
						} 

						text = text.toLowerCase();
						if ( text.search( filterString )  === -1 ) {
							rows[index].isVisible = false;
						} else {
							rows[index].isVisible = true;
						}
					}
				}
			}

			function filterDateSingleColum( rows, dataIndex, filter ){
				if ( filter.from === 0 && filter.to === 0 ) {
					for ( var index = 0; index < rows.length; index++ ) {
						rows[index].isVisible = true;
					}
				} else {
					for ( var index = 0; index < rows.length; index++ ) {
						let content = rows[index].data.find( obj => { return obj.dataIndex===dataIndex } ).content;
						let timestamp = getDateTimestamp( content );

						if ( filter.from > 0 && filter.to > 0 ) {
							if ( timestamp >= filter.from - 1 && timestamp <= filter.to + 1 ) {
								rows[index].isVisible = true;
							} else {
								rows[index].isVisible = false;
							}
						} else if ( filter.from > 0 ) {
							if ( timestamp >= filter.from ) {
								rows[index].isVisible = true;
							} else {
								rows[index].isVisible = false;
							}
						} else if ( filter.to > 0 ) {
							if ( timestamp >= filter.from ) {
								rows[index].isVisible = true;
							} else {
								rows[index].isVisible = false;
							}
						}
					}
				}
			}
  		},
		onToolbarToolClick: function( data ) {
			if ( data.hasOwnProperty( 'callback' ) ) {
				let callback = data.callback;

				if ( this.selectable === true ) {
					let callbackRows = [];
					for ( var index = 0; index < this.rows.length; index++ ) {
						let callbackRow = this.rows[index];
						callbackRow.data.splice( 0, 1 );
						callbackRows.push( callbackRow );
					}
					window[callback]( callbackRows );
				} else {
					window[callback]( this.rows );
				}
				
			}
		}
	}
};

var prepareColumns = function( cols, isSelectable ) {
	if ( isSelectable === true ) {
		let col = {
			label: '',
			type: 'selectable',
			dataIndex: 'selectable'
		}
		cols.unshift( col );
	}
	for ( var index = 0; index < cols.length; index++ ) {
		if ( cols[index].hasOwnProperty( 'sortable' ) === false ) {
			cols[index].sortable = false;
		} else {
			cols[index].ariaSorted = 'none';
		}
		if ( cols[index].hasOwnProperty( 'filterable' ) === false ) {
			cols[index].filterable = false;
		}
		if ( cols[index].sortable === true || cols[index].filterable === true )
		if ( cols[index].hasOwnProperty( 'options' ) === false ) {
			cols[index].options = getOptions( cols[index] );
		}
	}
}

var prepareRows = function( rows, availableRows, dataIndexColumnTypeMap, isSelectable, isSelected, renderer ) {
	for ( var index = 0; index < rows.length; index++ ) {
		rows[index].isVisible = true;
		let row = rows[index];
		if ( row.hasOwnProperty( 'rowIndex' ) === false ) {
			row.rowIndex = index+1;
		}

		if ( isSelectable === true ) {
			if ( row.hasOwnProperty( 'selected' ) === false ) {
				row.selected = isSelected;
			}

			let cell = {
				content: '',
				dataIndex: 'selectable',
				selected: isSelected
			}
			row.data.unshift( cell );
		}

		for ( var idx = 0; idx < row.data.length; idx++ ) {
			let cell = row.data[idx];
			cell.type = dataIndexColumnTypeMap[cell.dataIndex];

			if ( typeof renderer[cell.dataIndex] !== 'undefined') {
				cell.renderer = renderer[cell.dataIndex];
			}
		}

		// TODO: Bring row.data in order of availableDataIndex
		availableRows.push( row );
	}
}

var getOptions = function( col ) {
	let options = [];
	if ( col.sortable === true ) {
		sortOptions = getSortOptions( col.type );
		options = options.concat( sortOptions );
	}
	if ( col.filterable === true ) {
		filterOptions = getFilterOptions( col.type );
		options = options.concat( filterOptions );
	}
	return options;
}

var getSortOptions = function( type ) {
	let options = [ {
			name: 'SortASC',
			label: mw.message( 'vuejsplus-data-grid-option-sort-string-asc' ).toString()
		}, {
			name: 'SortDESC',
			label: mw.message( 'vuejsplus-data-grid-option-sort-string-desc' ).toString()
		} ];
	return options;
}

var getFilterOptions = function( type ) {
	let options = [];
	if ( type.toLowerCase() === 'string' || type.toLowerCase() === 'html' || type.toLowerCase() === 'numeric' ) {
		options = [ {
			name: 'StringFilter',
			label: mw.message( 'vuejsplus-data-grid-option-filter-string' ).toString()
		} ];
	}
	if ( type.toLowerCase() === 'date' ) {
		options = [ {
			name: 'DateFilter',
			label: mw.message( 'vuejsplus-data-grid-option-filter-date' ).toString()
		} ];
	}
	return options;
}
</script>

<style lang="css">
:root {
	--vuejsplus--focus-visible: blue;
}
.opt-menu {
	position: relative;
}
.opt-menu.opt-menu-btn{
	padding-right: 1.5em;
	background-image: url( '../../../VueJsPlus/resources/images/Bootstrap_three-dots-vertical.svg.png' );
	background-repeat: no-repeat;
	background-size: 1em;
	background-position-x: right;
	background-position-y: center;
}
.opt-menu button:focus-visible {
	outline: 0;
	box-shadow: 0 0 0 .1em var(--vuejsplus--focus-visible);
}
.vuejsplus-aria-lve {
	height: 0;
	overflow: hidden;
}
</style>