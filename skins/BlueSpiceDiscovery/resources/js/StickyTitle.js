( function ( $ ) {

	let initialTitlePos = 0;
	$( ( e ) => { // eslint-disable-line no-unused-vars
		initialTitlePos = getTitleLineContent().offset().top;
		if ( initialTitlePos === 'undefined' ) {
			initialTitlePos = 0;
		}

		$( window ).on( 'scroll', () => {
			if ( !isSpecialPage() && window.innerWidth >= 767 ) {
				setSickyTitleForVerticalPosition();
				handleVEToolbar();
			}
		} );

		$( window ).on( 'resize', () => {
			if ( ( window.innerWidth < 766 ) && $( 'body' ).hasClass( 'title-fixed' ) ) {
				disableStickyTitle();
			} else if ( window.innerWidth >= 767 ) {
				setSickyTitleForVerticalPosition();
				handleVEToolbar();
			}
		} );

		if ( $( 'body' ).hasClass( 'title-fixed' ) ) {
			const resizeObserver = new ResizeObserver( () => {
				alignTitleLine();
				resizeTitleLineContent();
			} );
			resizeObserver.observe( $( '#main' )[ 0 ] );
		}

	} );

	function setSickyTitleForVerticalPosition() {
		const windowTop = $( this ).scrollTop();
		const headerHeight = $( '#header > nav' ).height();

		if ( windowTop > ( initialTitlePos - headerHeight ) ) {
			enableSitckyTitle();
		} else {
			disableStickyTitle();
		}
	}

	function enableSitckyTitle() {
		$( 'body' ).addClass( 'title-fixed' );

		const $titleLine = getTitleLine();
		const $titleSection = getTitleSection();
		$titleSection.css( 'padding-top', $titleLine.height() );

		alignTitleLine();
		resizeTitleLineContent();
	}

	function disableStickyTitle() {
		$( 'body' ).removeClass( 'title-fixed' );

		const $titleSection = getTitleSection();
		$titleSection.removeAttr( 'style' );

		const $titleLineContent = getTitleLineContent();
		$titleLineContent.removeAttr( 'style' );
	}

	function handleVEToolbar() {
		if ( inEditMode() ) {
			if ( isStickyTitle() ) {
				setVEToolbarPosition();
			} else {
				resetVEToolbarPosition();
			}
		}
	}

	function isStickyTitle() {
		return $( 'body' ).hasClass( 'title-fixed' );
	}

	function isSpecialPage() {
		return $( 'body' ).hasClass( 'ns-special' );
	}

	function getTitleSection() {
		return $( '#title-section' );
	}

	function getTitleLine() {
		return $( '#title-line' );
	}

	function getTitleLineContent() {
		return $( '#title-line > div' );
	}

	function setTitleLineContentWidth( width ) {
		const $titleLineContent = getTitleLineContent();
		$titleLineContent.outerWidth( width );
	}

	function getWrapperTopPosition() {
		return $( '#wrapper' ).offset().top;
	}

	function getMainWidth() {
		return $( '#main' ).outerWidth();
	}

	function getMainPosLeft() {
		const offset = $( '#main' ).offset();
		return offset.left;
	}

	function getContentWidth() {
		return $( 'main' ).outerWidth();
	}

	function resizeTitleLineContent() {
		const mainWidth = getMainWidth();
		// MMV overlay triggers this resize method but with
		// var mainWidth = 0
		if ( mainWidth > 0 ) {
			setTitleLineContentWidth( mainWidth );
		}
	}

	function alignTitleLine() {
		const left = getMainPosLeft();
		$( 'body.title-fixed #title-line > div' ).css( 'margin-left', left );
	}

	function getVEToolbar() {
		return $( '#content .ve-init-target >.ve-ui-toolbar > .oo-ui-toolbar-bar' );
	}

	function setVEToolbarPosition() {
		const top = getWrapperTopPosition();
		const mainWidth = getContentWidth();
		const $titleLine = getTitleLine();
		const topPosition = $titleLine.height() + top;

		const $toolbar = getVEToolbar();
		$toolbar.css( 'top', topPosition );
		$toolbar.css( 'position', 'fixed' );
		$toolbar.width( mainWidth );

		const $titleSection = getTitleSection();
		$titleSection.css( 'padding-bottom', $toolbar.height() );
	}

	function resetVEToolbarPosition() {
		const $toolbar = getVEToolbar();
		$toolbar.removeAttr( 'style' );
	}

	function inEditMode() {
		const targetVisual = $( '.ve-init-target-visual' );
		const targetSource = $( '.ve-init-target-source' );

		if ( targetVisual.length || targetSource.length ) {
			return true;
		}

		return false;
	}

}( jQuery ) );
