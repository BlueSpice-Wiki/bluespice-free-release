<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author fdcn <fdcn64@gmail.com>, PhiLiP <philip.npc@gmail.com>
 */

namespace MediaWiki\Language;

use MediaWiki\Logger\LoggerFactory;
use StringUtils;

/**
 * The rules used for language conversion, this processes the rules
 * extracted by Parser from the `-{ }-` wikitext syntax.
 *
 * @ingroup Language
 */
class ConverterRule {
	/**
	 * @var string original text in -{text}-
	 */
	public $mText;
	/**
	 * @var LanguageConverter
	 */
	public $mConverter;
	/** @var string|false */
	public $mRuleDisplay = '';
	/** @var string|false */
	public $mRuleTitle = false;
	/**
	 * @var string the text of the rules
	 */
	public $mRules = '';
	/** @var string */
	public $mRulesAction = 'none';
	/** @var array */
	public $mFlags = [];
	/** @var array */
	public $mVariantFlags = [];
	/** @var array */
	public $mConvTable = [];
	/**
	 * @var array of the translation in each variant
	 */
	public $mBidtable = [];
	/**
	 * @var array of the translation in each variant
	 */
	public $mUnidtable = [];

	/**
	 * @param string $text The text between -{ and }-
	 * @param LanguageConverter $converter
	 */
	public function __construct( $text, LanguageConverter $converter ) {
		$this->mText = $text;
		$this->mConverter = $converter;
	}

	/**
	 * Check if the variant array is in the convert array.
	 *
	 * @param array|string $variants Variant language code
	 * @return string|false Translated text
	 */
	public function getTextInBidtable( $variants ) {
		$variants = (array)$variants;
		if ( !$variants ) {
			return false;
		}
		foreach ( $variants as $variant ) {
			if ( isset( $this->mBidtable[$variant] ) ) {
				return $this->mBidtable[$variant];
			}
		}
		return false;
	}

	/**
	 * Parse flags with syntax -{FLAG| ... }-
	 */
	private function parseFlags() {
		$text = $this->mText;
		$flags = [];
		$variantFlags = [];

		$sepPos = strpos( $text, '|' );
		if ( $sepPos !== false ) {
			$validFlags = $this->mConverter->getFlags();
			$f = StringUtils::explode( ';', substr( $text, 0, $sepPos ) );
			foreach ( $f as $ff ) {
				$ff = trim( $ff );
				if ( isset( $validFlags[$ff] ) ) {
					$flags[$validFlags[$ff]] = true;
				}
			}
			$text = substr( $text, $sepPos + 1 );
		}

		if ( !$flags ) {
			$flags['S'] = true;
		} elseif ( isset( $flags['R'] ) ) {
			// remove other flags
			$flags = [ 'R' => true ];
		} elseif ( isset( $flags['N'] ) ) {
			// remove other flags
			$flags = [ 'N' => true ];
		} elseif ( isset( $flags['-'] ) ) {
			// remove other flags
			$flags = [ '-' => true ];
		} elseif ( count( $flags ) === 1 && isset( $flags['T'] ) ) {
			$flags['H'] = true;
		} elseif ( isset( $flags['H'] ) ) {
			// replace A flag, and remove other flags except T
			$temp = [ '+' => true, 'H' => true ];
			if ( isset( $flags['T'] ) ) {
				$temp['T'] = true;
			}
			if ( isset( $flags['D'] ) ) {
				$temp['D'] = true;
			}
			$flags = $temp;
		} else {
			if ( isset( $flags['A'] ) ) {
				$flags['+'] = true;
				$flags['S'] = true;
			}
			if ( isset( $flags['D'] ) ) {
				unset( $flags['S'] );
			}
			// try to find flags like "zh-hans", "zh-hant"
			// allow syntaxes like "-{zh-hans;zh-hant|XXXX}-"
			$variantFlags = array_intersect( array_keys( $flags ), $this->mConverter->getVariants() );
			if ( $variantFlags ) {
				$variantFlags = array_fill_keys( $variantFlags, true );
				$flags = [];
			}
		}
		$this->mVariantFlags = $variantFlags;
		$this->mRules = $text;
		$this->mFlags = $flags;
	}

	/**
	 * Generate conversion table.
	 */
	private function parseRules() {
		$rules = $this->mRules;
		$bidtable = [];
		$unidtable = [];
		$varsep_pattern = $this->mConverter->getVarSeparatorPattern();

		// Split text according to $varsep_pattern, but ignore semicolons from HTML entities
		$rules = preg_replace( '/(&[#a-zA-Z0-9]+);/', "$1\x01", $rules );
		$choice = preg_split( $varsep_pattern, $rules );
		// @phan-suppress-next-line PhanTypeComparisonFromArray
		if ( $choice === false ) {
			$error = preg_last_error();
			$errorText = preg_last_error_msg();
			LoggerFactory::getInstance( 'parser' )->warning(
				'ConverterRule preg_split error: {code} {errorText}',
				[
					'code' => $error,
					'errorText' => $errorText
				]
			);
			$choice = [];
		}
		$choice = str_replace( "\x01", ';', $choice );

		foreach ( $choice as $c ) {
			$v = explode( ':', $c, 2 );
			if ( count( $v ) !== 2 ) {
				// syntax error, skip
				continue;
			}
			$to = trim( $v[1] );
			$v = trim( $v[0] );
			$u = explode( '=>', $v, 2 );
			$vv = $this->mConverter->validateVariant( $v );
			// if $to is empty (which is also used as $from in bidtable),
			// strtr() could return a wrong result.
			if ( count( $u ) === 1 && $to !== '' && $vv ) {
				$bidtable[$vv] = $to;
			} elseif ( count( $u ) === 2 ) {
				$from = trim( $u[0] );
				$v = trim( $u[1] );
				$vv = $this->mConverter->validateVariant( $v );
				// if $from is empty, strtr() could return a wrong result.
				if ( array_key_exists( $vv, $unidtable )
					&& !is_array( $unidtable[$vv] )
					&& $from !== ''
					&& $vv ) {
					$unidtable[$vv] = [ $from => $to ];
				} elseif ( $from !== '' && $vv ) {
					$unidtable[$vv][$from] = $to;
				}
			}
			// syntax error, pass
			if ( !isset( $this->mConverter->getVariantNames()[$vv] ) ) {
				$bidtable = [];
				$unidtable = [];
				break;
			}
		}
		$this->mBidtable = $bidtable;
		$this->mUnidtable = $unidtable;
	}

	/**
	 * @return string
	 */
	private function getRulesDesc() {
		$codesep = $this->mConverter->getDescCodeSeparator();
		$varsep = $this->mConverter->getDescVarSeparator();
		$text = '';
		foreach ( $this->mBidtable as $k => $v ) {
			$text .= $this->mConverter->getVariantNames()[$k] . "$codesep$v$varsep";
		}
		foreach ( $this->mUnidtable as $k => $a ) {
			foreach ( $a as $from => $to ) {
				$text .= $from . '⇒' . $this->mConverter->getVariantNames()[$k] .
					"$codesep$to$varsep";
			}
		}
		return $text;
	}

	/**
	 * Parse rules conversion.
	 *
	 * @param string $variant
	 *
	 * @return string
	 */
	private function getRuleConvertedStr( $variant ) {
		$bidtable = $this->mBidtable;
		$unidtable = $this->mUnidtable;

		if ( count( $bidtable ) + count( $unidtable ) === 0 ) {
			return $this->mRules;
		}

		// display current variant in bidirectional array
		$disp = $this->getTextInBidtable( $variant );
		// or display current variant in fallbacks
		if ( $disp === false ) {
			$disp = $this->getTextInBidtable(
				$this->mConverter->getVariantFallbacks( $variant ) );
		}
		// or display current variant in unidirectional array
		if ( $disp === false && array_key_exists( $variant, $unidtable ) ) {
			$disp = array_values( $unidtable[$variant] )[0];
		}
		// or display first text under disable manual convert
		if ( $disp === false && $this->mConverter->getManualLevel()[$variant] === 'disable' ) {
			if ( count( $bidtable ) > 0 ) {
				$disp = array_values( $bidtable )[0];
			} else {
				$disp = array_values( array_values( $unidtable )[0] )[0];
			}
		}

		return $disp;
	}

	/**
	 * Similar to getRuleConvertedStr(), but this prefers to use MediaWiki\Title\Title;
	 * use original page title if $variant === $this->mConverter->getMainCode(),
	 * and may return false in this case (so this title conversion rule
	 * will be ignored and the original title is shown).
	 *
	 * @since 1.22
	 * @param string $variant The variant code to display page title in
	 * @return string|false The converted title or false if just page name
	 */
	private function getRuleConvertedTitle( $variant ) {
		if ( $variant === $this->mConverter->getMainCode() ) {
			// If a string targeting exactly this variant is set,
			// use it. Otherwise, just return false, so the real
			// page name can be shown (and because variant === main,
			// there'll be no further automatic conversion).
			$disp = $this->getTextInBidtable( $variant );
			if ( $disp ) {
				return $disp;
			}
			if ( array_key_exists( $variant, $this->mUnidtable ) ) {
				$disp = array_values( $this->mUnidtable[$variant] )[0];
			}
			// Assigned above or still false.
			return $disp;
		}

		return $this->getRuleConvertedStr( $variant );
	}

	/**
	 * Generate conversion table for all text.
	 */
	private function generateConvTable() {
		// Special case optimisation
		if ( !$this->mBidtable && !$this->mUnidtable ) {
			$this->mConvTable = [];
			return;
		}

		$bidtable = $this->mBidtable;
		$unidtable = $this->mUnidtable;
		$manLevel = $this->mConverter->getManualLevel();

		$vmarked = [];
		foreach ( $this->mConverter->getVariants() as $v ) {
			/* for bidirectional array
				fill in the missing variants, if any,
				with fallbacks */
			if ( !isset( $bidtable[$v] ) ) {
				$variantFallbacks =
					$this->mConverter->getVariantFallbacks( $v );
				$vf = $this->getTextInBidtable( $variantFallbacks );
				if ( $vf ) {
					$bidtable[$v] = $vf;
				}
			}

			if ( isset( $bidtable[$v] ) ) {
				foreach ( $vmarked as $vo ) {
					// use syntax: -{A|zh:WordZh;zh-tw:WordTw}-
					// or -{H|zh:WordZh;zh-tw:WordTw}-
					// or -{-|zh:WordZh;zh-tw:WordTw}-
					// to introduce a custom mapping between
					// words WordZh and WordTw in the whole text
					if ( $manLevel[$v] === 'bidirectional' ) {
						$this->mConvTable[$v][$bidtable[$vo]] = $bidtable[$v];
					}
					if ( $manLevel[$vo] === 'bidirectional' ) {
						$this->mConvTable[$vo][$bidtable[$v]] = $bidtable[$vo];
					}
				}
				$vmarked[] = $v;
			}
			/* for unidirectional array fill to convert tables */
			if ( ( $manLevel[$v] === 'bidirectional' || $manLevel[$v] === 'unidirectional' )
				&& isset( $unidtable[$v] )
			) {
				if ( isset( $this->mConvTable[$v] ) ) {
					$this->mConvTable[$v] = $unidtable[$v] + $this->mConvTable[$v];
				} else {
					$this->mConvTable[$v] = $unidtable[$v];
				}
			}
		}
	}

	/**
	 * Parse rules and flags.
	 * @param string|null $variant Variant language code
	 */
	public function parse( $variant = null ) {
		if ( !$variant ) {
			$variant = $this->mConverter->getPreferredVariant();
		}

		$this->parseFlags();
		$flags = $this->mFlags;

		// convert to specified variant
		// syntax: -{zh-hans;zh-hant[;...]|<text to convert>}-
		if ( $this->mVariantFlags ) {
			// check if current variant in flags
			if ( isset( $this->mVariantFlags[$variant] ) ) {
				// then convert <text to convert> to current language
				$this->mRules = $this->mConverter->autoConvert( $this->mRules,
					$variant );
			} else {
				// if the current variant is not in flags,
				// then we check its fallback variants.
				$variantFallbacks =
					$this->mConverter->getVariantFallbacks( $variant );
				if ( is_array( $variantFallbacks ) ) {
					foreach ( $variantFallbacks as $variantFallback ) {
						// if current variant's fallback exist in flags
						if ( isset( $this->mVariantFlags[$variantFallback] ) ) {
							// then convert <text to convert> to fallback language
							$this->mRules =
								$this->mConverter->autoConvert( $this->mRules,
									$variantFallback );
							break;
						}
					}
				}
			}
			$this->mFlags = $flags = [ 'R' => true ];
		}

		if ( !isset( $flags['R'] ) && !isset( $flags['N'] ) ) {
			// decode => HTML entities modified by Sanitizer::internalRemoveHtmlTags
			$this->mRules = str_replace( '=&gt;', '=>', $this->mRules );
			$this->parseRules();
		}
		$rules = $this->mRules;

		if ( !$this->mBidtable && !$this->mUnidtable ) {
			if ( isset( $flags['+'] ) || isset( $flags['-'] ) ) {
				// fill all variants if the text in -{A/H/-|text}- is non-empty but without rules
				if ( $rules !== '' ) {
					foreach ( $this->mConverter->getVariants() as $v ) {
						$this->mBidtable[$v] = $rules;
					}
				}
			} elseif ( !isset( $flags['N'] ) && !isset( $flags['T'] ) ) {
				$this->mFlags = $flags = [ 'R' => true ];
			}
		}

		$this->mRuleDisplay = false;
		foreach ( $flags as $flag => $unused ) {
			switch ( $flag ) {
				case 'R':
					// if we don't do content convert, still strip the -{}- tags
					$this->mRuleDisplay = $rules;
					break;
				case 'N':
					// process N flag: output current variant name
					$ruleVar = trim( $rules );
					$this->mRuleDisplay = $this->mConverter->getVariantNames()[$ruleVar] ?? '';
					break;
				case 'D':
					// process D flag: output rules description
					$this->mRuleDisplay = $this->getRulesDesc();
					break;
				case 'H':
					// process H,- flag or T only: output nothing
					$this->mRuleDisplay = '';
					break;
				case '-':
					$this->mRulesAction = 'remove';
					$this->mRuleDisplay = '';
					break;
				case '+':
					$this->mRulesAction = 'add';
					$this->mRuleDisplay = '';
					break;
				case 'S':
					$this->mRuleDisplay = $this->getRuleConvertedStr( $variant );
					break;
				case 'T':
					$this->mRuleTitle = $this->getRuleConvertedTitle( $variant );
					$this->mRuleDisplay = '';
					break;
				default:
					// ignore unknown flags (but see error-case below)
			}
		}
		if ( $this->mRuleDisplay === false ) {
			$this->mRuleDisplay = '<span class="error">'
				. wfMessage( 'converter-manual-rule-error' )->inContentLanguage()->escaped()
				. '</span>';
		}

		$this->generateConvTable();
	}

	/**
	 * Checks if there are conversion rules.
	 * @return bool
	 */
	public function hasRules() {
		return $this->mRules !== '';
	}

	/**
	 * Get display text on markup -{...}-
	 * @return string
	 */
	public function getDisplay() {
		return $this->mRuleDisplay;
	}

	/**
	 * Get converted title.
	 * @return string|false
	 */
	public function getTitle() {
		return $this->mRuleTitle;
	}

	/**
	 * Return how to deal with conversion rules.
	 * @return string
	 */
	public function getRulesAction() {
		return $this->mRulesAction;
	}

	/**
	 * Get conversion table. (bidirectional and unidirectional
	 * conversion table)
	 * @return array
	 */
	public function getConvTable() {
		return $this->mConvTable;
	}

	/**
	 * Get conversion rules string.
	 * @return string
	 */
	public function getRules() {
		return $this->mRules;
	}

	/**
	 * Get conversion flags.
	 * @return array
	 */
	public function getFlags() {
		return $this->mFlags;
	}
}

/** @deprecated class alias since 1.43 */
class_alias( ConverterRule::class, 'ConverterRule' );
