<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class PageContext extends RequestContext {

	/**
	 * @param Title $title
	 * @param User $user
	 * @param array|null $data
	 * @param IContextSource|null $originalContext
	 */
	public function __construct(
		Title $title,
		User $user,
		?array $data = [],
		?IContextSource $originalContext = null
	) {
		$this->setUser( $user );
		$this->setTitle( $title );
		$revId =
			$data['rev-id'] ?? $originalContext?->getRequest()->getInt( 'oldid', null ) ?? $title->getLatestRevID();
		$this->setRequest( new FauxRequest(
			array_merge(
				$originalContext?->getRequest()->getValues() ?? [],
				[ 'title' => $title->getPrefixedDBkey() ],
				[ 'oldid' => $revId ],
				$data
			)
		) );
	}
}
