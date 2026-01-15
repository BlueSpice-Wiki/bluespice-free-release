<?php

namespace BlueSpice\UserSidebar\Component\SimpleCard;

use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Sanitizer;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;

class PersonalMenu extends SimpleCard {

	/**
	 * @var string
	 */
	protected $section = null;

	/**
	 * @var array
	 */
	protected $links = null;

	/**
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 * @param string $section
	 * @param array $links
	 */
	public function __construct( string $section, array $links ) {
		$this->section = $section;
		$this->links = $links;
		parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		$id = Sanitizer::escapeIdForAttribute( $this->section );
		return "umcus-$id";
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		return [ 'card-mn' ];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$this->context = $context;
		return !empty( $this->links );
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$id = strtolower( Sanitizer::escapeIdForAttribute( $this->section ) );
		$services = MediaWikiServices::getInstance();
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );

		$links = $this->links;

		return [
			new SimpleCardHeader( [
				'id' => "umcus-menu-head-$id",
				'classes' => [ 'menu-title' ],
				'items' => [
					new Literal(
						"umcus-menu-title-$id",
						htmlspecialchars( $this->section )
					)
				]
			] ),
			new SimpleLinklistGroupFromArray( [
				'id' => "umcus-manu-$id-linkgroup",
				'classes' => [ 'menu-card-body', 'menu-list', 'll-dft' ],
				'links' => $linkFormatter->formatLinks( $links ),
				'role' => 'group',
				'item-role' => 'presentation',
				'aria' => [
					'labelledby' => "umcus-menu-head-$id"
				]
			] ),
		];
	}
}
