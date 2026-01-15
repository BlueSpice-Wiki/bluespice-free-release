window.bs = window.bs || {};
window.bs.skin = bs.skin || {};
bs.skin.enhancedSidebar = bs.skin.enhancedSidebar || {};
bs.skin.enhancedSidebar.tree = bs.skin.enhancedSidebar.tree || {};
bs.skin.enhancedSidebar.tree.node = bs.skin.enhancedSidebar.tree.node || {};

bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode = function ( cfg ) {
	bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode.parent.call( this, cfg );
};

OO.inheritClass( bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode, ext.menueditor.ui.data.node.TreeNode );

bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode.prototype.getFormConfig = function () {
	// Adapt value of "hidden" field to the format expected by the GroupMultiselect widget
	return {
		listeners: {
			beforeSubmitData: function ( data ) {
				if ( data.hasOwnProperty( 'hidden' ) && typeof data.hidden === 'object' ) {
					if ( data.hidden.length === 0 ) {
						data.hidden = '';
						return data;
					}
					data.hidden = '{{#ifingroup: ' + data.hidden.join( ',' ) + '|false|true}}';
				}
				return data;
			},
			parseComplete: function ( items ) {
				if ( this.data.hasOwnProperty( 'hidden' ) ) {
					// {{#ifingroup: sysop,editor|false|true}}
					// Regex out group names
					const groups = this.data.hidden.match( /{{#ifingroup: ([^|]+)\|false\|true}}/ );
					if ( groups && groups.length > 1 ) {
						let showForGroups = groups[ 1 ].split( ',' );
						showForGroups = showForGroups.map( ( group ) => group.trim() );

						// This is needed to update the size of the dialog after setting value
						items.hidden.connect( this, {
							change: function () {
								this.invokeFormListeners( 'renderComplete' );
							}
						} );
						items.hidden.setValue( showForGroups );
					}
				}
			}
		}
	};
};

bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode.prototype.getCustomFormFields = function ( dialog ) { // eslint-disable-line no-unused-vars
	return [];
};

bs.skin.enhancedSidebar.tree.node.EnhancedSidebarNode.prototype.getFormFields = function ( dialog ) {
	return [
		{
			name: 'text',
			type: 'text',
			required: true,
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-text' ).text(),
			help: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-text-help' ).text()
		}
	].concat( this.getCustomFormFields( dialog ), [
		{
			name: 'hidden',
			type: 'group_multiselect',
			widget_$overlay: dialog.$overlay,
			widget_groupTypes: [ 'core-minimal', 'extension-minimal', 'custom' ],
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-show-for-groups-text' ).text()
		}, {
			name: 'classes',
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-classes' ).text(),
			type: 'tag',
			widget_allowArbitrary: true
		}, {
			name: 'icon-cls',
			// Probably too much for users
			hidden: true,
			type: 'text',
			label: mw.message( 'bs-discovery-enhanced-mediawiki-sidebar-field-icon-cls' ).text()
		}
	] );
};
