bs.util.registerNamespace( 'ext.bluespice.about.ui' );

const USER_PREFERENCE_DONTSHOWUNTIL = 'bs-about-banner-rating-dontshowuntil';

ext.bluespice.about.ui.BannerRating = function () {
	ext.bluespice.about.ui.BannerRating.super.call( this, { padded: false, expanded: false } );

	const messages = mw.config.get( 'bsAboutBannerRatingMessages' );

	const heading = document.createElement( 'h3' );
	heading.innerText = messages.heading;
	heading.className = 'bs-about-banner-heading';

	const message = document.createElement( 'p' );
	message.innerText = messages.message;
	message.className = 'bs-about-banner-text';

	// Subscribe / Rate
	const subscribeRateButton = new OO.ui.ButtonWidget( {
		href: messages.link,
		target: '_blank',
		label: messages.subscriberate,
		classes: [ 'bs-about-banner-button' ]
	} );

	// Remind in 1 week
	const remindButton = new OO.ui.ButtonWidget( {
		label: messages.remind,
		classes: [ 'bs-about-banner-button' ]
	} )
		.on( 'click', () => {
			const oneWeekFromNow = Math.floor( Date.now() / 1000 ) + ( 7 * 24 * 60 * 60 );
			new mw.Api().saveOption( USER_PREFERENCE_DONTSHOWUNTIL, oneWeekFromNow.toString() );
			$( '#bs-about-banner-rating-container' ).remove();
		} );

	// Hide forever
	const closeButton = new OO.ui.ButtonWidget( {
		icon: 'close',
		framed: false,
		classes: [ 'bs-about-banner-button-close' ]
	} )
		.on( 'click', () => {
			new mw.Api().saveOption( USER_PREFERENCE_DONTSHOWUNTIL, '9999999999' );
			$( '#bs-about-banner-rating-container' ).remove();
			this.emit( 'removedRating' );
		} );

	this.$element.append(
		$( '<div>' ).addClass( 'bs-about-banner-content-container' ).append(
			$( '<div>' ).addClass( [
				'bs-about-banner-icon-container',
				'bs-about-banner-icon-rating'
			] ),
			$( '<div>' ).addClass( 'bs-about-banner-textbutton-container' ).append(
				heading,
				message,
				subscribeRateButton.$element,
				remindButton.$element
			)
		),
		closeButton.$element
	);
};

OO.inheritClass( ext.bluespice.about.ui.BannerRating, OO.ui.PanelLayout );
