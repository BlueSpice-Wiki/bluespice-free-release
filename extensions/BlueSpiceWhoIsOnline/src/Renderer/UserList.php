<?php

namespace BlueSpice\WhoIsOnline\Renderer;

use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use BlueSpice\UtilityFactory;
use BlueSpice\WhoIsOnline\Data\Record;
use HtmlArmor;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MWException;
use MWStake\MediaWiki\Component\DataStore\ResultSet;

class UserList extends \BlueSpice\Renderer {
	public const PARAM_RECORD_SET = 'recordset';

	/**
	 *
	 * @var ResultSet
	 */
	protected $recordSet = null;

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

	/**
	 * @var int
	 */
	protected $targetId = 0;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param UtilityFactory|null $util
	 */
	protected function __construct(
		Config $config, Params $params, ?LinkRenderer $linkRenderer = null,
		?IContextSource $context = null, $name = '', ?UtilityFactory $util = null
	) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );

		$this->util = $util;

		$this->args[static::PARAM_TAG] = $params->get(
			static::PARAM_TAG,
			'ul'
		);
		$this->recordSet = $params->get(
			static::PARAM_RECORD_SET,
			null
		);
		$this->targetId = $params->get( 'target', 0 );
		if ( !$this->recordSet instanceof ResultSet ) {
			throw new MWException(
				"Param '" . static::PARAM_RECORD_SET . "' must be an instance of '"
				. ResultSet::class . "'!"
			);
		}
	}

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param Params $params
	 * @param IContextSource|null $context
	 * @param LinkRenderer|null $linkRenderer
	 * @param UtilityFactory|null $util
	 * @return Renderer
	 */
	public static function factory(
		$name, MediaWikiServices $services, Config $config, Params $params,
		?IContextSource $context = null, ?LinkRenderer $linkRenderer = null, ?UtilityFactory $util = null
	) {
		if ( !$context ) {
			$context = $params->get(
				static::PARAM_CONTEXT,
				false
			);
			if ( !$context instanceof IContextSource ) {
				$context = RequestContext::getMain();
			}
		}
		if ( !$linkRenderer ) {
			$linkRenderer = $services->getLinkRenderer();
		}
		if ( !$util ) {
			$util = $services->getService( 'BSUtilityFactory' );
		}

		return new static( $config, $params, $linkRenderer, $context, $name, $util );
	}

	/**
	 *
	 * @return string
	 */
	private function renderList() {
		$out = '';
		$out .= Html::openElement( 'ul' );
		if ( $this->recordSet->getTotal() < 1 ) {
			$out .= Html::element(
				'li',
				[],
				$this->msg( 'bs-whoisonline-nousers' )->plain()
			);
		}
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		foreach ( $this->recordSet->getRecords() as $record ) {
			$user = $userFactory->newFromId( $record->get( Record::USER_ID ) );
			if ( !$user ) {
				continue;
			}
			$displayName = $this->util->getUserHelper( $user )->getDisplayName();
			$out .= Html::openElement( 'li' );
			$out .= $this->linkRenderer->makeLink(
				$user->getUserPage(),
				new HtmlArmor( $displayName )
			);
			$out .= Html::closeElement( 'li' );
		}
		$out .= Html::closeElement( 'ul' );
		return $out;
	}

	/**
	 *
	 * @return string
	 */
	public function render() {
		// wrap the list in a template for bootstrap tooltip
		$out = '<div id="' . $this->targetId . '"
			class="tooltip bs-tooltip wo-tooltip" role="tooltip">';
		$out .= '<div class="tooltip-arrow"></div>';
		$out .= '<div class="tooltip-body">';
		// content of '.tooltip-inner' element will be replaced by link attribute 'title'
		$out .= '<div class="tooltip-inner"></div>';
		$out .= $this->renderList();
		$out .= '</div>';
		$out .= '</div>';

		return $out;
	}

}
