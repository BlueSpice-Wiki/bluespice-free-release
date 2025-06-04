bs.util.registerNamespace( 'bs.hidetitle.ui.plugin' );

bs.hidetitle.ui.plugin.MWMetaDialog = function BsHideTitleUiPluginMWMetaDialog( component ) { // eslint-disable-line no-unused-vars
	bs.hidetitle.ui.plugin.MWMetaDialog.super.apply( this, arguments );
};

OO.inheritClass( bs.hidetitle.ui.plugin.MWMetaDialog, bs.vec.ui.plugin.MWMetaDialog );

bs.hidetitle.ui.plugin.MWMetaDialog.prototype.initialize = function () {
	this.component.advancedSettingsPage.hideTitle = new OO.ui.FieldLayout(
		new OO.ui.ButtonSelectWidget()
			.addItems( [
				new OO.ui.ButtonOptionWidget( {
					data: 'default',
					label: mw.msg( 'bs-hidetitle-ve-dialog-meta-settings-showtitle' )
				} ),
				new OO.ui.ButtonOptionWidget( {
					data: 'mw:PageProp/bs_hidetitle',
					label: mw.msg( 'bs-hidetitle-ve-dialog-meta-settings-hidetitle' )
				} )
			] )
			.connect( this, { select: 'onHideTitleChange' } ),
		{
			$overlay: this.component.$overlay,
			align: 'top',
			label: mw.msg( 'bs-hidetitle-ve-dialog-meta-settings-hidetitle-label' ),
			help: mw.msg( 'bs-hidetitle-ve-dialog-meta-settings-hidetitle-label-help' )
		}
	);

	this.component.advancedSettingsPage.advancedSettingsFieldset.$element.append( this.component.advancedSettingsPage.hideTitle.$element );
};

bs.hidetitle.ui.plugin.MWMetaDialog.prototype.getSetupProcess = function ( parentProcess, data ) {
	const advancedSettingsPage = this.component.advancedSettingsPage;
	this.component.advancedSettingsPage.setup( data.fragment, data );
	const metaList = data.fragment.getSurface().metaList;

	const hideTitleField = advancedSettingsPage.hideTitle.getField();
	advancedSettingsPage.metaList = metaList;
	const hideTitleOption = advancedSettingsPage.getMetaItem( 'bsHideTitle' );
	const hideTitleData = hideTitleOption ? 'mw:PageProp/bs_hidetitle' : 'default';

	hideTitleField
		.selectItemByData( hideTitleData );

	return parentProcess;
};

bs.hidetitle.ui.plugin.MWMetaDialog.prototype.getTeardownProcess = function ( parentProcess, data ) { // eslint-disable-line no-unused-vars
	const advancedSettingsPage = this.component.advancedSettingsPage;

	const hideTitleOption = advancedSettingsPage.getMetaItem( 'bsHideTitle' );
	const hideTitleData = advancedSettingsPage.hideTitle.getField().findSelectedItem();

	if ( hideTitleOption ) {
		advancedSettingsPage.fragment.removeMeta( hideTitleOption );
	}
	if ( hideTitleData.data !== 'default' ) {
		const newHideTitleItem = { type: 'bsHideTitle' };
		this.component.getFragment().insertMeta( newHideTitleItem, 0 );
	}

	return parentProcess;
};

/**
 * Handle option state change events.
 */
bs.vec.ui.plugin.MWMetaDialog.prototype.onHideTitleChange = function () {
	this.component.actions.setAbilities( {
		done: true
	} );
};
