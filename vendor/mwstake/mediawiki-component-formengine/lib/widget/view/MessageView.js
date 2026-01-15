mw.ext.forms.widget.view.MessageView = function ( cfg ) {
	cfg.label = cfg.title || cfg.label;
	mw.ext.forms.widget.view.MessageView.parent.call( this, cfg );
};

OO.inheritClass( mw.ext.forms.widget.view.MessageView, OO.ui.MessageWidget );
