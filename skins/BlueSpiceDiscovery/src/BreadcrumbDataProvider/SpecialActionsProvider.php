<?php

namespace BlueSpice\Discovery\BreadcrumbDataProvider;

use MediaWiki\Language\RawMessage;
use MediaWiki\Title\Title;

class SpecialActionsProvider extends BaseBreadcrumbDataProvider {

	/**
	 *
	 * @var string
	 */
	private $action = '';

	/**
	 * @param Title $title
	 * @return Title
	 */
	public function getRelevantTitle( $title ): Title {
		if ( isset( $this->webRequestValues['target'] ) && !$title->isSpecialPage() ) {
			$pagename = $this->webRequestValues['target'];
			$this->action = $title->getBaseText();
		} elseif ( isset( $this->webRequestValues['page'] ) ) {
			$pagename = $this->webRequestValues['page'];
			$this->action = $title->getBaseText();
		} else {
			$bits = explode( '/', $title->getText() );
			$this->action = array_shift( $bits );
			$pagename = '';

			foreach ( $bits as $bit ) {
				if ( $bit != end( $bits ) ) {
					$pagename .= $bit . '/';
				} else {
					$pagename .= $bit;
				}
			}
		}

		$newTitle = $this->titleFactory->newFromText( $pagename );

		// Prevent null Title error in case of square bracket use in Special:ReplaceText
		if ( $newTitle == null ) {
			return $title;
		}

		if ( $newTitle->isTalkPage() ) {
			$this->talkName = true;
		}

		return $newTitle;
	}

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getLabels( $title ): array {
		if ( $this->talkName === true ) {
			$labels[] = [
				'text' => $this->messageLocalizer->msg( 'bs-discovery-breadcrumb-label-talk' )
			];
		}
		$msgSpecialKey = 'bs-discovery-breadcrumb-label-special-' . strtolower( $this->action );
		$msgSpecialText = $this->messageLocalizer->msg( $msgSpecialKey );
		if ( !$msgSpecialText->exists() ) {
			$msgSpecialText = new RawMessage( $this->action );
		}
		$labels[] = [
			'text' => $msgSpecialText
		];
		if ( isset( $this->webRequestValues['action'] ) ) {
			$msgActionKey = 'bs-discovery-breadcrumb-label-action-' . $this->webRequestValues['action'];
			$msgActionText = $this->messageLocalizer->msg( $msgActionKey );
			if ( !$msgActionText->exists() ) {
				$msgActionText = new RawMessage( $this->webRequestValues['action'] );
			}
			$labels[] = [ 'text' => $msgActionText ];
		}
		return $labels;
	}

	/**
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function applies( Title $title ): bool {
		if ( $title->isSpecialPage() && strpos( $title->getBaseText(), '/' ) ) {
			return true;
		}
		if ( $title->isSpecialPage() && isset( $this->webRequestValues['target'] ) &&
			$this->webRequestValues['target'] != "" ) {
			return true;
		}
		if ( $title->isSpecialPage() && isset( $this->webRequestValues['page'] ) &&
			$this->webRequestValues['page'] != "" ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function isSelfLink( $node ): bool {
		return false;
	}
}
