bs.util.registerNamespace( 'bs.whoisonline.util.tag' );
bs.whoisonline.util.tag.CountDefinition = function BsVecUtilTagCountDefinition() {
	bs.whoisonline.util.tag.CountDefinition.super.call( this );
};

OO.inheritClass( bs.whoisonline.util.tag.CountDefinition, bs.vec.util.tag.Definition );

bs.whoisonline.util.tag.CountDefinition.prototype.getCfg = function () {
	const cfg = bs.whoisonline.util.tag.CountDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'Whoisonlinecount',
		name: 'whoisonlinecount',
		tagname: 'bs:whoisonlinecount',
		descriptionMsg: 'bs-whoisonline-tag-whoisonlinecount-description',
		menuItemMsg: 'bs-whoisonline-ve-whoisonlinecountinspector-title'
	} );
};

bs.vec.registerTagDefinition(
	new bs.whoisonline.util.tag.CountDefinition()
);
