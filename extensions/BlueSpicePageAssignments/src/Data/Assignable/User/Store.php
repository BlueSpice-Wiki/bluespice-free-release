<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class Store extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore\Store {

	/** @var Title */
	protected $contextTitle;

	/**
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param LinkRenderer $linkRenderer
	 * @param TitleFactory $titleFactory
	 * @param GlobalVarConfig $mwsgConfig
	 * @param Title $contextTitle
	 */
	public function __construct(
		ILoadBalancer $lb, UserFactory $userFactory, LinkRenderer $linkRenderer,
		TitleFactory $titleFactory, GlobalVarConfig $mwsgConfig, Title $contextTitle
	) {
		parent::__construct( $lb, $userFactory, $linkRenderer, $titleFactory, $mwsgConfig );
		$this->contextTitle = $contextTitle;
	}

	/**
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			$this->lb, $this->userFactory, $this->linkRenderer,
			$this->titleFactory, $this->mwsgConfig, $this->contextTitle
		);
	}

}
