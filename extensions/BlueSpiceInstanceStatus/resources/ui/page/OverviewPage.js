bs.util.registerNamespace( 'bs.instanceStatus.ui' );

bs.instanceStatus.ui.OverviewPage = function ( name, config ) {
	bs.instanceStatus.ui.OverviewPage.parent.call( this, name, config );
	this.info = mw.config.get( 'bsInstanceStatus_StatusData' );

	this.layout = new OO.ui.FieldsetLayout();
	this.layout.$element.append( this.renderItems() );

	this.$element.append( this.layout.$element );
};

OO.inheritClass( bs.instanceStatus.ui.OverviewPage, OO.ui.PageLayout );

bs.instanceStatus.ui.OverviewPage.prototype.setupOutlineItem = function () {
	this.outlineItem.setLabel( mw.message( 'bs-instancestatus-instance-status-main-label' ).text() );
};

bs.instanceStatus.ui.OverviewPage.prototype.renderItems = function () {
	const layouts = [];
	for ( const key in this.info ) {
		if ( !this.info.hasOwnProperty( key ) ) {
			continue;
		}
		const data = this.info[ key ];
		const items = [];
		if ( Object.prototype.hasOwnProperty.call( data, 'icon' ) ) {
			items.push( new OO.ui.IconWidget( { icon: data.icon } ) );
		}
		items.push( new OO.ui.LabelWidget( { label: data.label } ) );

		const layout = new OO.ui.HorizontalLayout( {
			classes: [ 'bs-instance-status-overview-item' ],
			items: items
		} );

		const $value = $( '<div>' ).addClass( 'bs-instance-status-overview-item-value' ).html( data.value );
		layout.$element.append( $value );

		layouts.push( layout.$element );
	}

	return layouts;
};

bs.instanceStatus.registry.Panels.register( 'overview', bs.instanceStatus.ui.OverviewPage );
