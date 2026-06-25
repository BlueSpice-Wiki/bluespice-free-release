<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs;

use Collation;
use MediaWiki\Collation\CollationFactory;
use MediaWiki\Language\Language;

trait ContentLanguageCollationTrait {
	private ?Collation $resolvedCollation = null;

	protected function resolveCollation(
		CollationFactory $collationFactory,
		Language $contentLanguage
	): Collation {
		if ( $this->resolvedCollation ) {
			return $this->resolvedCollation;
		}

		$languageFallback = \MediaWiki\MediaWikiServices::getInstance()->getLanguageFallback();
		$code = $contentLanguage->getCode();
		$normalized = preg_replace( '/-(formal|informal)$/i', '', $code ) ?: $code;

		$candidates = array_values( array_unique(
			array_merge( [ $normalized ], $languageFallback->getAll( $normalized ) )
		) );

		foreach ( $candidates as $candidate ) {
			try {
				$c = $collationFactory->makeCollation( 'uca-' . $candidate );
				$c->getFirstLetter( 'a' );
				$this->resolvedCollation = $c;
				return $c;
			} catch ( \RuntimeException $e ) {
				continue;
			}
		}

		// should never reach here though
		$fallback = $collationFactory->makeCollation( 'uca-en' );
		$fallback->getFirstLetter( 'a' );
		$this->resolvedCollation = $fallback;
		return $fallback;
	}
}
