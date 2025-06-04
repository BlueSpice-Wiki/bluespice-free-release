<?php

namespace BlueSpice\About\HookHandlers\BeforePageDisplay;

use MediaWiki\MediaWikiServices;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\User\User;

class AddBanners implements BeforePageDisplayHook {

	private const USER_PREFERENCE_NEWSLETTER_DONTSHOW = 'bs-about-banner-newsletter-dontshow';
	private const USER_PREFERENCE_RATING_DONTSHOWUNTIL = 'bs-about-banner-rating-dontshowuntil';

	private const NEWSLETTER_MESSAGES = [
		'en' => [
			'heading' => 'Stay up to date!',
			'message' => 'Subscribe to our newsletter and receive the latest information on updates, features, webinars and events from the world of BlueSpice.', // phpcs:ignore Generic.Files.LineLength.TooLong
			'subscriberate' => 'Subscribe now',
			'link' => 'https://bluespice.com/?mtm_campaign=Product-NewsletterSubscription#newsletter'
		],
		'de' => [
			'heading' => 'Bleiben Sie auf dem Laufenden!',
			'message' => 'Abonnieren Sie unseren Newsletter und erhalten Sie die neuesten Infos zu Updates, Features, Webinaren und Events aus der Welt von BlueSpice.', // phpcs:ignore Generic.Files.LineLength.TooLong
			'subscriberate' => 'Jetzt abonnieren',
			'link' => 'https://bluespice.com/de/?mtm_campaign=Produkt-NewsletterSubskription#newsletter'
		],
		'fr' => [
			'heading' => 'Restez informé !',
			'message' => 'Abonnez-vous à notre newsletter et recevez les dernières informations sur les mises à jour, les fonctionnalités, les webinaires et les événements autour de BlueSpice.', // phpcs:ignore Generic.Files.LineLength.TooLong
			'subscriberate' => 'Abonnez-vous maintenant',
			'link' => 'https://bluespice.com/?mtm_campaign=Product-NewsletterSubscription#newsletter'
		]
	];

	private const RATING_MESSAGES = [
		'en' => [
			'heading' => 'Your opinion counts!',
			'message' => 'Take five minutes and rate BlueSpice at Capterra.',
			'subscriberate' => 'Rate now',
			'remind' => 'Remind me later',
			'link' => 'https://reviews.capterra.com/products/new/bd5d69ea-dd9f-405d-a333-a820004f3204/?mtm_campaign=Product-GartnerReview' // phpcs:ignore Generic.Files.LineLength.TooLong
		],
		'de' => [
			'heading' => 'Ihre Meinung zählt!',
			'message' => 'Nehmen Sie sich fünf Minuten Zeit und bewerten Sie BlueSpice bei Capterra.',
			'subscriberate' => 'Jetzt bewerten',
			'remind' => 'Später erinnern',
			'link' => 'https://reviews.capterra.com/products/new/bd5d69ea-dd9f-405d-a333-a820004f3204/?lang=de&mtm_campaign=Produkt-GartnerReview' // phpcs:ignore Generic.Files.LineLength.TooLong
		],
		'fr' => [
			'heading' => 'Votre avis compte !',
			'message' => 'Prenez cinq minutes pour évaluer BlueSpice chez Capterra.',
			'subscriberate' => 'Évaluer maintenant',
			'remind' => 'Plus tard',
			'link' => 'https://reviews.capterra.com/products/new/bd5d69ea-dd9f-405d-a333-a820004f3204/?lang=fr&mtm_campaign=Product_GartnerReview_fr' // phpcs:ignore Generic.Files.LineLength.TooLong
		]
	];

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $this->getEdition() !== 'free' ) {
			return;
		}

		$user = $skin->getUser();
		if ( $user->isAnon() ) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		$userOptionsLookup = $services->getUserOptionsLookup();
		$hideNewsletter = $userOptionsLookup->getOption( $user, self::USER_PREFERENCE_NEWSLETTER_DONTSHOW );
		$hideRatingUntil = $userOptionsLookup->getOption( $user, self::USER_PREFERENCE_RATING_DONTSHOWUNTIL );
		$hideRating = false;
		if ( $hideRatingUntil > time() ) {
			$hideRating = true;
		}

		if ( $hideNewsletter && $hideRating ) {
			return;
		}

		$userLang = $this->getSupportedLanguage( $user );
		$isSysop = in_array( 'sysop', $services->getUserGroupManager()->getUserGroups( $user ) );

		if ( $isSysop && !$hideNewsletter ) {
			$out->addJsConfigVars( 'bsAboutBannerNewsletterMessages', self::NEWSLETTER_MESSAGES[ $userLang ] );
			$out->addModules( [ 'ext.bluespice.about.banner.newsletter' ] );
			$out->addModuleStyles( [ 'ext.bluespice.about.banner.newsletter.styles' ] );
		}

		if ( !$hideRating ) {
			$out->addJsConfigVars( 'bsAboutBannerRatingMessages', self::RATING_MESSAGES[ $userLang ] );
			$out->addModules( [ 'ext.bluespice.about.banner.rating' ] );
			$out->addModuleStyles( [ 'ext.bluespice.about.banner.rating.styles' ] );
		}
	}

	/**
	 * @return string
	 */
	private function getEdition(): string {
		return $this->getFileContent( $GLOBALS['IP'] . '/BLUESPICE-EDITION' );
	}

	/**
	 * Reads a file, sanitises its contents, and trims whitespace.
	 *
	 * @param string $filePath
	 * @return string
	 */
	private function getFileContent( string $filePath ): string {
		$objectCache = MediaWikiServices::getInstance()->getObjectCacheFactory()->getLocalServerInstance();

		return $objectCache->getWithSetCallback(
			$objectCache->makeKey( 'bluespiceabout-getedition', $filePath ),
			$objectCache::TTL_HOUR,
			static function () use ( $filePath ) {
				$content = '';
				if ( file_exists( $filePath ) ) {
					$fileContent = file_get_contents( $filePath );
					$content = Sanitizer::stripAllTags( $fileContent );
					$content = trim( $content );
				}

				return $content;
			}
		);
	}

	/**
	 * @param User $user
	 * @return string
	 */
	private function getSupportedLanguage( User $user ): string {
		$supportedLanguages = [ 'en', 'de', 'fr' ];
		$services = MediaWikiServices::getInstance();

		$userLanguage = $services->getUserOptionsLookup()->getOption( $user, 'language' );
		if ( !in_array( $userLanguage, $supportedLanguages ) ) {
			$userLanguage = $services->getContentLanguage()->getCode();
			if ( !in_array( $userLanguage, $supportedLanguages ) ) {
				$userLanguage = 'en';
			}
		}

		return $userLanguage;
	}

}
