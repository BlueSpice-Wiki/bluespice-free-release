bs.util.registerNamespace( 'bs.extendedstatistics.util.tag' );
bs.extendedstatistics.util.tag.ProgressDefinition = function BsVecUtilTagProgressDefinition() {
	bs.extendedstatistics.util.tag.ProgressDefinition.super.call( this );
};

OO.inheritClass( bs.extendedstatistics.util.tag.ProgressDefinition, bs.vec.util.tag.Definition );

bs.extendedstatistics.util.tag.ProgressDefinition.prototype.getCfg = function () {
	const cfg = bs.extendedstatistics.util.tag.ProgressDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, { // eslint-disable-line no-jquery/no-extend
		classname: 'Progress',
		name: 'progress',
		tagname: 'bs:statistics:progress',
		descriptionMsg: 'bs-statistics-tag-progress-desc',
		menuItemMsg: 'bs-statistics-ve-progressinspector-title',
		attributes: [ {
			name: 'basecount',
			labelMsg: 'bs-statistics-ve-progress-attr-basecount-label',
			helpMsg: 'bs-statistics-ve-progress-attr-basecount-help',
			type: 'number',
			default: '200'
		}, {
			name: 'baseitem',
			labelMsg: 'bs-statistics-ve-progress-attr-baseitem-label',
			helpMsg: 'bs-statistics-ve-progress-attr-baseitem-help',
			type: 'text',
			default: ''
		}, {
			name: 'progressitem',
			labelMsg: 'bs-statistics-ve-progress-attr-progressitem-label',
			helpMsg: 'bs-statistics-ve-progress-attr-progressitem-help',
			type: 'text',
			default: 'OK'
		}, {
			name: 'width',
			labelMsg: 'bs-statistics-ve-progress-attr-width-label',
			helpMsg: 'bs-statistics-ve-progress-attr-width-help',
			type: 'number',
			default: '150'
		} ]
	} );
};

bs.vec.registerTagDefinition(
	new bs.extendedstatistics.util.tag.ProgressDefinition()
);
