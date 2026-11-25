<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Title\Title;

class PageLabelHelper {

	/**
	 * @param Title $title
	 * @param string $titleText
	 * @param bool $showNamespace
	 * @return string
	 */
	public function getTitleText(
		Title $title, string $titleText, bool $showNamespace
	): string {
		if ( $showNamespace === true ) {
			// Already namespace-prefixed text must not be re-prefixed, or it would be duplicated
			if ( $titleText === $title->getPrefixedText() ) {
				return $titleText;
			} elseif ( $titleText === $title->getSubpageText() ) {
				return $this->replace(
					$title->getSubpageText(),
					$title->getPrefixedText(),
					$titleText
				);
			} elseif ( $titleText === $title->getText() ) {
				return $this->replace(
					$title->getText(),
					$title->getPrefixedText(),
					$titleText
				);
			}
		} elseif ( $showNamespace === false ) {
			if ( $titleText === $title->getPrefixedText() ) {
				return $this->replace(
					$title->getPrefixedText(),
					$title->getSubpageText(),
					$titleText
				);
			} elseif ( $titleText === $title->getSubpageText() ) {
				return $titleText;
			} elseif ( $titleText === $title->getText() ) {
				return $this->replace(
					$title->getText(),
					$title->getSubpageText(),
					$titleText
				);
			}
		}

		return $titleText;
	}

	/**
	 * @param string $search
	 * @param string $replace
	 * @param string $text
	 * @return string
	 */
	private function replace(
		string $search, string $replace, string $text
	): string {
		if ( $text === $search ) {
			$newText = str_replace(
				$search, $replace, $text
			);
			if ( is_string( $newText ) ) {
				return $newText;
			}
		}
		return $text;
	}
}
