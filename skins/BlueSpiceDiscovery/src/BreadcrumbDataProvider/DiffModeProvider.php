<?php

namespace BlueSpice\Discovery\BreadcrumbDataProvider;

use MediaWiki\Title\Title;

class DiffModeProvider extends BaseBreadcrumbDataProvider {

	/**
	 * @inheritDoc
	 */
	public function getLabels( $title ): array {
		return [ 'text' => $this->messageLocalizer->msg( 'bs-discovery-breadcrumb-label-diff' ) ];
	}

	/**
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function applies( Title $title ): bool {
		if ( isset( $this->webRequestValues['diff'] ) ) {
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
