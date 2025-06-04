ext.notifyme.ui.widget.MailEditingHelp = function ( data, cfg ) {
	cfg = cfg || {};
	this.helpData = data;
	this.$textarea = cfg.$textarea;

	cfg.framed = false;
	cfg.icon = 'help';
	cfg.label = mw.msg( 'notifyme-mail-template-edit-help-button-label' );
	ext.notifyme.ui.widget.MailEditingHelp.parent.call( this, cfg );

	this.popup = new OO.ui.PopupWidget( {
		$content: this.makeLayout(),
		padded: false,
		$floatableContainer: $( '.wikiEditor-ui-top' ),
		autoClose: true,
		anchor: false,
		width: this.$textarea.outerWidth(),
		position: 'below',
		classes: [ 'ext-notifyme-mail-template-edit-help' ]
	} );

	this.$element.append( this.popup.$element );

	this.connect( this, {
		click: function () {
			this.popup.toggle();
		}
	} );
};

OO.inheritClass( ext.notifyme.ui.widget.MailEditingHelp, OO.ui.ButtonWidget );

ext.notifyme.ui.widget.MailEditingHelp.prototype.makeLayout = function () {
	this.layout = new OO.ui.BookletLayout( {
		expanded: false,
		outlined: true,
		padded: false
	} );
	this.layout.addPages( this.makePages( this.helpData ) );

	return this.layout.$element;
};

ext.notifyme.ui.widget.MailEditingHelp.prototype.makePages = function ( data ) {
	const types = [ 'logo', 'params', 'colors', 'int' ],
		pages = [];
	for ( let i = 0; i < types.length; i++ ) {
		pages.push( this.makePage( types[ i ], data ) );
	}

	return pages;
};

ext.notifyme.ui.widget.MailEditingHelp.prototype.makePage = function ( type, data ) {
	const page = function ( t, data, parent ) { // eslint-disable-line no-shadow
		this.type = t;
		this.pageData = data;
		page.parent.call( this, 'mailtemplate-help-' + this.type, { expanded: false, padded: true } );

		const headerMsg = mw.message( 'notifyme-mail-template-edit-help-' + this.type ); // eslint-disable-line mediawiki/msg-doc
		if ( headerMsg.exists() ) {
			this.$element.append( headerMsg.plain() );
		}
		const renderFunction = 'render' + this.type.charAt( 0 ).toUpperCase() + this.type.slice( 1 );
		if ( typeof parent[ renderFunction ] === 'function' ) {
			this.$element.append( parent[ renderFunction ]( this.pageData ) );
		}
	};

	OO.inheritClass( page, OO.ui.PageLayout );

	page.prototype.setupOutlineItem = function () {
		this.outlineItem.setLabel( mw.message( 'notifyme-mail-template-edit-help-category-' + this.type ).text() ); // eslint-disable-line mediawiki/msg-doc
	};

	return new page( type, data[ type ] || {}, this ); // eslint-disable-line new-cap
};

ext.notifyme.ui.widget.MailEditingHelp.prototype.renderColors = function ( data ) {
	const $container = $( '<div>' ).addClass( 'ext-notifyme-mail-template-edit-help-color-container' ),
		layouts = [];
	for ( const name in data ) {
		if ( !data.hasOwnProperty( name ) ) {
			continue;
		}
		const layout = new OO.ui.HorizontalLayout(
			{ align: 'top', classes: [ 'ext-notifyme-mail-template-edit-help-color-layout' ] }
		);
		const $color = $( '<div>' ).addClass( 'ext-notifyme-mail-template-edit-help-color-color' ).css( 'background-color', data[ name ] );
		layout.$element.append(
			$color,
			new OO.ui.LabelWidget( { label: '@' + name } ).$element
		);
		layouts.push( layout.$element );
	}

	const exampleLayout = new OO.ui.HorizontalLayout( {
		items: [ new OO.ui.LabelWidget( {
			label: mw.msg( 'notifyme-mail-template-edit-help-header-example' )
		} ) ]
	} );
	exampleLayout.$element.append(
		$( '<div>' )
			.css( {
				'background-color': '#dbdbdb',
				width: '100%',
				padding: '10px'
			} ).text( '<div style="background-color: @colorName;">' )
	);

	$container.append( layouts );
	$container.append( exampleLayout.$element );
	return $container;
};

ext.notifyme.ui.widget.MailEditingHelp.prototype.renderParams = function ( data ) {
	const $table = $( '<table>' ).addClass( 'ext-notifyme-mail-template-edit-help-params-table' ),
		$thead = $( '<thead>' ).addClass( 'ext-notifyme-mail-template-edit-help-params-thead' ),
		$tbody = $( '<tbody>' ).addClass( 'ext-notifyme-mail-template-edit-help-params-tbody' );

	$thead.append(
		$( '<tr>' ).append(
			$( '<th>' ).text( mw.msg( 'notifyme-mail-template-edit-help-header-variable' ) ),
			$( '<th>' ).text( mw.msg( 'notifyme-mail-template-edit-help-header-example' ) ),
			$( '<th>' ).text( mw.msg( 'notifyme-mail-template-edit-help-header-description' ) )
		)
	);
	$table.append( $thead );
	this.renderParamRows( data, $tbody );
	$table.append( $tbody );

	return $table;
};

ext.notifyme.ui.widget.MailEditingHelp.prototype.renderParamRows = function ( data, $tbody ) {
	for ( const param in data ) {
		if ( !data.hasOwnProperty( param ) ) {
			continue;
		}
		const paramConfig = data[ param ];
		const $row = $( '<tr>' );
		const $exampleCell = $( '<td>' ).html( '<pre>' + paramConfig.example + '</pre>' );
		$row.append(
			$( '<td>' ).html( '<b>' + param + '</b>' ),
			$exampleCell,
			$( '<td>' ).html( paramConfig.desc )
		);
		$tbody.append( $row );
		if ( paramConfig.type === 'array' && paramConfig.hasOwnProperty( 'items' ) && param !== 'notifications' ) {
			$exampleCell.attr( 'rowspan', Object.keys( paramConfig.items ).length + 1 );
			for ( const subItemParam in paramConfig.items ) {
				if ( !paramConfig.items.hasOwnProperty( subItemParam ) ) {
					continue;
				}
				const item = paramConfig.items[ subItemParam ];
				$tbody.append( $( '<tr>' ).addClass( 'sub' ).append(
					$( '<td>' ).html( '<b>' + subItemParam + '</b>' ),
					$( '<td>' ).html( item.desc )
				) );
			}
		}
		if ( param === 'notifications' ) {
			// Exception for "notifications" param, as there we need to render it as a separate table
			// (as there is not enough space for 3+ level nesting)
			$tbody.append( $( '<tr>' ).addClass( 'sub-title' ).append(
				'<td colspan="3"><b>' + mw.msg( 'notifyme-mail-template-edit-help-header-notifications' ) + '</b></td>'
			) );
			this.renderParamRows( paramConfig.items, $tbody );
		}
	}
};
