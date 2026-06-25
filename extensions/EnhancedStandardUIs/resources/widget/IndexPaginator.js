ext = ext || {};
ext.enhancedUI = ext.enhancedUI || {};
ext.enhancedUI.widget = ext.enhancedUI.widget || {};

ext.enhancedUI.widget.IndexPaginator = function ( cfg ) {
	cfg = cfg || {};
	ext.enhancedUI.widget.IndexPaginator.parent.call( this, cfg );

	this.preventReload = false;
	this.panel = cfg.panel;

	this.picker = new OO.ui.ButtonSelectWidget();
	this.picker.connect( this, {
		select: 'onItemSelected'
	} );

	this.store = cfg.store;
	this.store.connect( this, {
		onBucketChange: 'onBuckets'
	} );

	this.$element.append( this.picker.$element );
	this.$element.addClass( 'enhanced-ui-index-paginator' );
	this.$element.attr( 'aria-label',
		mw.message( 'enhanced-standard-uis-allpages-panel-index-paginator-aria-label' ).text() );
};

OO.inheritClass( ext.enhancedUI.widget.IndexPaginator, OO.ui.Widget );

ext.enhancedUI.widget.IndexPaginator.prototype.onBuckets = function ( buckets ) {
	if ( this.preventReload || this.store.sortkeyFilterActive ) {
		return;
	}
	this.picker.clearItems();
	if (
		!buckets ||
		Object.keys( buckets ).indexOf( 'sortkey' ) === -1 ||
		Object.keys( buckets.sortkey ).length < 2
	) {
		this.panel.$indexPaginator.addClass( 'hidden' );
		return;
	}
	this.panel.$indexPaginator.removeClass( 'hidden' );
	const sortKeys = buckets.sortkey;
	const items = [
		new OO.ui.ButtonOptionWidget( {
			data: '_all',
			label: '*',
			title: mw.message( 'enhanced-standard-uis-allpages-panel-index-paginator-all-label' ).text(),
			framed: false
		} )
	];
	for ( const key in sortKeys ) {
		if ( sortKeys.hasOwnProperty( key ) ) {
			items.push( new OO.ui.ButtonOptionWidget( {
				data: key,
				label: key,
				framed: false
			} ) );
		}
	}
	this.picker.addItems( items );
};

ext.enhancedUI.widget.IndexPaginator.prototype.onItemSelected = async function ( item ) {
	if ( item && item.getData() ) {
		this.preventReload = true;
		let data = [];
		if ( item.getData() === '_all' ) {
			data = await this.store.removeSortkeyFilter();
		} else {
			data = await this.store.loadForSortkey( item.getData() );
		}
		this.preventReload = false;
		this.panel.setPages( data );
	}
};
