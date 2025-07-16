<template>
	<toolbar
		:items="tools"
		:has-cancel-button="true"
		@toolclick="onToolbarToolClick"
		@cancel="onToolbarCancel"
	></toolbar>

	<cdx-dialog
		v-model:open="open"
		title="Help"
		:use-close-button="true"
		@default="open=false"
		@primary="open=false"
		class="pdfcreator-editor-help-dialog"
	>
	<cdx-table
		class="pdfcreator-editor-help-table"
		:columns="columns"
		:data="data"
		:use-row-headers="true"
	>
	</cdx-table>
	</cdx-dialog>

</template>

<script>
	const { defineComponent, ref } = require( 'vue' );
	const { Toolbar } = require( './../vuejsplus.js' );
	const { CdxDialog, CdxButton, CdxTable } = require( '@wikimedia/codex' );

	module.exports = defineComponent( {
		name: 'EditorToolbar',
		components: {
			Toolbar,
			CdxDialog,
			CdxButton,
			CdxTable
		},
		props: {
			tools: {
				type: Array,
				default: []
			},
			config: {
				type: Object
			}
		},
		setup( props ) {
			const open = ref( false );
			const columns = [
				{ id: 'help-key', label: mw.message( 'pdfcreator-help-table-column-param-key' ).text() },
				{ id: 'help-desc', label: mw.message( 'pdfcreator-help-table-column-param-desc' ).text() },
			];

			let data = [];
			for ( var param in props.config ) {
				data.push( {
					'help-key': param,
					'help-desc': props.config[param]
				} );
			}

			return {
				open,
				columns,
				data
			}
		},
		data: function () {
			return {
				tools: this.tools,
				config: this.config,
				cancelButton: this.hasCancelButton
			};
		},
		methods: {
			onToolbarToolClick( toolData ) {
				if ( toolData.id === 'save' ) {
					let event = new CustomEvent( 'pdfcreator_editor_save' );
					document.dispatchEvent( event );
				} else if ( toolData.id === 'help' ) {
					this.open = true;
				}
			},
			onToolbarCancel() {
				const title = mw.Title.newFromText( mw.config.get( 'wgRelevantPageName' ) );
				window.location.href = title.getUrl();
			}
		}
	} );
</script>
<style lang="css">
	.pdfcreator-editor-help-dialog {
		max-width: 50rem;
		height: 100%;
	}

	.pdfcreator-editor-help-table .cdx-table__header {
		display: none;
	}
</style>