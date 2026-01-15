bs.util.registerNamespace( 'bs.whoisonline.util.tag' );
bs.whoisonline.util.tag.PopUpDefinition = function BsVecUtilTagPopUpDefinition() {
	bs.whoisonline.util.tag.PopUpDefinition.super.call( this );
};

OO.inheritClass( bs.whoisonline.util.tag.PopUpDefinition, bs.vec.util.tag.Definition );

bs.whoisonline.util.tag.PopUpDefinition.prototype.getCfg = function () {
	const cfg = bs.whoisonline.util.tag.PopUpDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'Whoisonlinepopup',
		name: 'whoisonlinepopup',
		tagname: 'bs:whoisonlinepopup',
		descriptionMsg: 'bs-whoisonline-tag-whoisonlinepopup-description',
		menuItemMsg: 'bs-whoisonline-ve-whoisonlinepopupinspector-title'
	} );
};

bs.vec.registerTagDefinition(
	new bs.whoisonline.util.tag.PopUpDefinition()
);
