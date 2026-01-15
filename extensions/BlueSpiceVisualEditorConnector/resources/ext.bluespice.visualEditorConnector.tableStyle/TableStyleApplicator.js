bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.TableStyleApplicator = function ( cfg ) {
	this.$element = cfg.domEl;
	this.tableStyleRegistry = bs.vec.registry.TableStyle.registry;
};

OO.initClass( bs.vec.ui.TableStyleApplicator );

bs.vec.ui.TableStyleApplicator.prototype.apply = function ( model ) {
	const modelClone = ve.cloneObject( model );
	const styles = modelClone.attributes || {};
	let styleAttr;
	let styleAttrValue;

	for ( styleAttr in styles ) {
		if ( !styles.hasOwnProperty( styleAttr ) ) {
			continue;
		}
		if ( !this.tableStyleRegistry.hasOwnProperty( styleAttr ) ) {
			continue;
		}

		styleAttrValue = styles[ styleAttr ];
		this.tableStyleRegistry[ styleAttr ].setValue( styleAttrValue );
		this.tableStyleRegistry[ styleAttr ].decorate( this.$element );
	}
};
