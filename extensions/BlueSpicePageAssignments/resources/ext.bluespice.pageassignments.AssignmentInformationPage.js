( ( mw, bs ) => {
	bs.util.registerNamespace( 'bs.pageassignments.info' );

	bs.pageassignments.info.AssignmentsInformationPage = function AssignmentsInformationPage( name, config ) {
		this.assignmentGrid = null;
		bs.pageassignments.info.AssignmentsInformationPage.super.call( this, name, config );
	};

	OO.inheritClass( bs.pageassignments.info.AssignmentsInformationPage, StandardDialogs.ui.BasePage ); // eslint-disable-line no-undef

	bs.pageassignments.info.AssignmentsInformationPage.prototype.setupOutlineItem = function () {
		bs.pageassignments.info.AssignmentsInformationPage.super.prototype.setupOutlineItem.apply( this, arguments );

		if ( this.outlineItem ) {
			this.outlineItem.setLabel( mw.message( 'bs-pageassignment-info-dialog' ).plain() );
		}
	};

	bs.pageassignments.info.AssignmentsInformationPage.prototype.setup = function () {
		return;
	};

	bs.pageassignments.info.AssignmentsInformationPage.prototype.onInfoPanelSelect = async function () {
		if ( !this.assignmentGrid ) {
			const title = mw.Title.newFromText( this.pageName );
			const namespace = title.getNamespaceId();
			const text = title.getMain();

			await mw.loader.using( [ 'ext.oOJSPlus.data', 'oojs-ui.styles.icons-user' ] );

			const assignmentsStore = new OOJSPlus.ui.data.store.RemoteStore( {
				action: 'bs-pageassignment-store',
				pageSize: 10,
				filter: {
					page_namespace: { // eslint-disable-line camelcase
						value: namespace,
						operator: 'eq',
						type: 'string'
					},
					page_title: { // eslint-disable-line camelcase
						value: text,
						operator: 'eq',
						type: 'string'
					}
				}
			} );

			const rawData = await assignmentsStore.doLoadData();
			const pageData = Object.values( rawData );
			const assignmentsData = pageData.length > 0 ? pageData[ 0 ].assignments : [];

			this.assignmentGrid = new OOJSPlus.ui.data.GridWidget( {
				style: 'differentiate-rows',
				columns: {
					pa_assignee_key: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-pageassignments-column-assignedto' ).text(),
						type: 'text',
						valueParser: ( value, row ) => {
							if ( row.pa_assignee_type === 'user' ) {
								const userWidget = new OOJSPlus.ui.widget.UserWidget( {
									user_name: value, // eslint-disable-line camelcase
									showLink: true,
									showRawUsername: false
								} );

								return new OO.ui.HtmlSnippet( userWidget.$element );
							}

							if ( row.pa_assignee_type === 'group' ) {
								const iconWidget = new OO.ui.IconWidget( {
									icon: 'userGroup'
								} );
								iconWidget.$element.css( {
									'margin-left': '5px',
									'margin-right': '20px'
								} );
								const labelWidget = new OOJSPlus.ui.widget.LabelWidget( {
									label: value
								} );

								return new OO.ui.HtmlSnippet(
									[ iconWidget.$element, labelWidget.$element ]
								);
							}
						}
					},
					pa_assignee_type: { // eslint-disable-line camelcase
						headerText: mw.message( 'bs-pageassignments-column-assignee-type' ).text(),
						type: 'text'
					}
				},
				data: assignmentsData
			} );

			this.$element.append( this.assignmentGrid.$element );
		}
	};

	registryPageInformation.register( 'assignment_infos', bs.pageassignments.info.AssignmentsInformationPage ); // eslint-disable-line no-undef

} )( mediaWiki, blueSpice );
