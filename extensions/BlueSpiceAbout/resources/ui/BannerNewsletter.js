bs.util.registerNamespace( 'ext.bluespice.about.ui' );

const USER_PREFERENCE_DONTSHOW = 'bs-about-banner-newsletter-dontshow';

ext.bluespice.about.ui.BannerNewsletter = function () {
	ext.bluespice.about.ui.BannerNewsletter.super.call( this, { padded: false, expanded: false } );

	const messages = mw.config.get( 'bsAboutBannerNewsletterMessages' );

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

	// Hide forever
	const closeButton = new OO.ui.ButtonWidget( {
		icon: 'close',
		framed: false,
		classes: [ 'bs-about-banner-button-close' ]
	} )
		.on( 'click', () => {
			new mw.Api().saveOption( USER_PREFERENCE_DONTSHOW, '1' );
			$( '#bs-about-banner-newsletter-container' ).remove();
		} );

	this.$element.append(
		$( '<div>' ).addClass( 'bs-about-banner-content-container' ).append(
			$( '<div>' ).addClass( [
				'bs-about-banner-icon-container',
				'bs-about-banner-icon-newsletter'
			] ),
			$( '<div>' ).addClass( 'bs-about-banner-textbutton-container' ).append(
				heading,
				message,
				subscribeRateButton.$element
			)
		),
		closeButton.$element
	);
};

OO.inheritClass( ext.bluespice.about.ui.BannerNewsletter, OO.ui.PanelLayout );
