bs.util.registerNamespace( 'ext.bluespice.pageassignments.ui.panel' );

ext.bluespice.pageassignments.ui.panel.Manager = function ( cfg ) {
	cfg = cfg || {};
	this.showUnassignedActive = false;

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-pageassignment-store',
		pageSize: 25,
		filter: {
			has_assignments: { // eslint-disable-line camelcase
				type: 'boolean',
				value: true
			}
		},
		sorter: {
			page_prefixedtext: { // eslint-disable-line camelcase
				direction: 'ASC'
			}
		}
	} );

	this.gridCfg = this.setupGridConfig();
	cfg.grid = this.gridCfg;

	ext.bluespice.pageassignments.ui.panel.Manager.parent.call( this, cfg );
};

OO.inheritClass( ext.bluespice.pageassignments.ui.panel.Manager, OOJSPlus.ui.panel.ManagerGrid );

ext.bluespice.pageassignments.ui.panel.Manager.prototype.setupGridConfig = function () {
	const gridCfg = {
		multiSelect: false,
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			page_prefixedtext: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-pageassignments-column-title' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => new OO.ui.HtmlSnippet( mw.html.element(
					'a',
					{
						href: mw.util.getUrl( value )
					},
					value
				) )
			},
			assignments: {
				headerText: mw.message( 'bs-pageassignments-column-assignments' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => this.makeAssignmentsWidget( value )
			}
		},
		actions: {
			edit: {
				headerText: mw.message( 'bs-pageassignments-header-action-edit' ).text(),
				title: mw.message( 'bs-pageassignments-title-edit' ).text(),
				type: 'action',
				actionId: 'edit',
				icon: 'edit',
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30
			},
			secondaryActions: {
				type: 'secondaryActions',
				actions: [ {
					label: mw.message( 'bs-pageassignments-action-log' ).text(),
					title: mw.message( 'bs-pageassignments-action-log' ).text(),
					data: 'log',
					icon: 'article'
				} ],
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30
			},
			delete: {
				headerText: mw.message( 'bs-pageassignments-header-action-delete' ).text(),
				title: mw.message( 'bs-pageassignments-title-delete' ).text(),
				type: 'action',
				actionId: 'delete',
				icon: 'trash',
				invisibleHeader: true,
				visibleOnHover: true,
				width: 30
			}
		},
		store: this.store,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();
					const $table = $( '<table>' );

					const $thead = $( '<thead>' )
						.append( $( '<tr>' )
							.append( $( '<th>' ).text( mw.message( 'bs-pageassignments-column-title' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-pageassignments-column-assignments' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-readconfirmation-column-read' ).text() ) ) // BlueSpiceReadConfirmation
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) {
							const record = response[ id ];
							const assignees = record.assignments.map( ( assignment ) => assignment.pa_assignee_key ).join( ' - ' ); // CSV comma delimiter
							let read = record.all_assignees_have_read; // BlueSpiceReadConfirmation
							read = read === 'disabled' ? mw.message( 'bs-readconfirmation-disabled-ns-short' ).text() : read;

							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.page_prefixedtext ) )
								.append( $( '<td>' ).text( assignees ) )
								.append( $( '<td>' ).text( read ) )
							);
						}
					}

					$table.append( $thead, $tbody );

					deferred.resolve( `<table>${ $table.html() }</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	mw.hook( 'BSPageAssignmentsManagerPanelInit' ).fire( gridCfg );

	// Move action columns to the end
	gridCfg.columns = Object.assign( gridCfg.columns, gridCfg.actions );

	return gridCfg;
};

ext.bluespice.pageassignments.ui.panel.Manager.prototype.getToolbarActions = function () {
	return [
		new OOJSPlus.ui.toolbar.tool.ToolbarTool( {
			name: 'showDisabled',
			icon: 'block',
			displayBothIconAndLabel: true,
			title: mw.msg( 'bs-pageassignments-show-unassigned-pages' ),
			callback: ( toolInstance ) => {
				this.showUnassignedActive = !this.showUnassignedActive;
				this.store.filter( new OOJSPlus.ui.data.filter.Boolean( {
					value: !this.showUnassignedActive
				} ), 'has_assignments' );
				toolInstance.setActive( this.showUnassignedActive );
			}
		} )
	];
};

ext.bluespice.pageassignments.ui.panel.Manager.prototype.onAction = function ( action, row ) {
	switch ( action ) {
		case 'edit': {
			const assignmentsPage = new bs.pageassignments.ui.AssignmentsPage( {
				data: {
					page: row.page_id,
					assignments: row.assignments
				}
			} );
			this.showPageassignmentsDialog( assignmentsPage );
			break;
		}
		case 'delete': {
			bs.util.confirm(
				'bs-pa-remove',
				{
					textMsg: 'bs-pageassignments-action-delete-confirm'
				},
				{
					ok: () => {
						this.onRemovePageassignmentsOk( row.page_id );
					}
				}
			);
			break;
		}
		case 'log': {
			window.location.href = mw.util.getUrl(
				'Special:Log', {
					page: row.page_prefixedtext,
					type: 'bs-pageassignments'
				}
			);
			break;
		}
		// Dynamic secondaryActions handler (BlueSpiceReadConfirmation)
		default: {
			// Find the matching action by its 'data' property
			const matchingAction = this.gridCfg.actions.secondaryActions.actions.find(
				( actionObj ) => actionObj.data === action
			);

			if ( matchingAction ) {
				matchingAction.doActionOnRow( row );
			}
			break;
		}
	}
};

ext.bluespice.pageassignments.ui.panel.Manager.prototype.showPageassignmentsDialog = async function ( assignmentsPage ) {
	const dialog = new OOJSPlus.ui.dialog.BookletDialog( {
		id: 'bs-pageassignments-set',
		title: mw.message( 'bs-pageassignments-dlg-title' ).plain(),
		pages: [ assignmentsPage ]
	} );

	const result = await dialog.show().closed;
	if ( result.success ) {
		this.store.reload();
	}
};

ext.bluespice.pageassignments.ui.panel.Manager.prototype.onRemovePageassignmentsOk = async function ( pageId ) {
	const result = await bs.api.tasks.execSilent( 'pageassignment', 'edit', {
		pageId: pageId,
		pageAssignments: []
	} );

	if ( result.success ) {
		this.store.reload();
	}
};

ext.bluespice.pageassignments.ui.panel.Manager.prototype.makeAssignmentsWidget = function ( assignments ) {
	if ( !assignments.length ) {
		return '';
	}

	const $widgets = $( '<div>' ).addClass( 'assignments-widgets' );

	assignments.forEach( ( assignment ) => {
		let $widget;
		if ( assignment.pa_assignee_type === 'user' ) {
			const userWidget = new OOJSPlus.ui.widget.UserWidget( {
				user_name: assignment.pa_assignee_key, // eslint-disable-line camelcase
				showLink: true,
				showRawUsername: false
			} );
			$widget = userWidget.$element;
		}

		if ( assignment.pa_assignee_type === 'group' ) {
			const iconWidget = new OO.ui.IconWidget( {
				icon: 'userGroup'
			} );
			iconWidget.$element.css( {
				'margin-left': '5px',
				'margin-right': '20px'
			} );
			const labelWidget = new OOJSPlus.ui.widget.LabelWidget( {
				label: assignment.pa_assignee_key
			} );

			$widget = $( '<div>' )
				.addClass( 'assignments-widgets-group' )
				.append( iconWidget.$element )
				.append( labelWidget.$element );
		}

		$widgets.append( $widget );
	} );

	return $widgets;
};
