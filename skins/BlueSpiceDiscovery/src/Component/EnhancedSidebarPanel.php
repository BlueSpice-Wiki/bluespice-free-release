<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\CookieHandler;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\TreeDataGenerator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class EnhancedSidebarPanel extends SimpleCard implements LoggerAwareInterface {

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var string
	 */
	private $id = '';

	/**
	 * @var string
	 */
	private $heading = '';

	/**
	 * @var array
	 */
	private $classes = [];

	/**
	 * @var array
	 */
	private $items = [];

	/**
	 * @var TreeDataGenerator
	 */
	private $treeDataGenerator;

	/**
	 * @var CookieHandler
	 */
	protected $cookieHandler = null;

	/**
	 * @param string $id
	 * @param string $heading
	 * @param array $items
	 */
	public function __construct( string $id, string $heading, array $classes, array $items,
		TreeDataGenerator $treeDataGenerator, CookieHandler $cookieHandler
	) {
		parent::__construct( [] );

		$this->id = $id;
		$this->heading = $heading;
		$this->classes = $classes;
		$this->items = $items;
		$this->treeDataGenerator = $treeDataGenerator;
		$this->cookieHandler = $cookieHandler;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id . '-pnl';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		$defaultClasses = [ 'w-100', 'bg-transp', 'tree-component' ];
		$classes = array_merge( $defaultClasses, $this->classes );
		return $classes;
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$header = new SimpleCardHeader( [
			'id' => $this->id . '-head',
			'classes' => [ 'menu-title' ],
			'items' => [
				new Literal(
					$this->id . '-head',
					$this->heading
				)
			]
		] );

		$body = new EnhancedSidebarTree(
			$this->items,
			$this->treeDataGenerator,
			$this->cookieHandler,
			[
				'id' => $this->id . '-menu',
				'classes' => [ 'w-100', 'bg-transp' ],
				'role' => 'tree',
				'aria' => [
					'labelledby' => $this->id . '-head'
				]
			]
		);

		$body->setLogger( $this->logger );

		return [ $header, $body ];
	}
}
