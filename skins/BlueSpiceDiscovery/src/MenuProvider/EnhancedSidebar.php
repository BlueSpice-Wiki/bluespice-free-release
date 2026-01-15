<?php

namespace BlueSpice\Discovery\MenuProvider;

use BlueSpice\Discovery\Component\EnhancedSidebarContainer;
use BlueSpice\Discovery\CookieHandler;
use BlueSpice\Discovery\IMenuProvider;
use MediaWiki\Language\RawMessage;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\TreeDataGenerator;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;
use Psr\Log\LoggerInterface;

class EnhancedSidebar implements IMenuProvider {

	/**
	 * @var string
	 */
	private $id = '';

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var RevisionStore
	 */
	private $revisionStore;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var ParserFactory
	 */
	private $parserFactory;

	/**
	 * @var TreeDataGenerator
	 */
	private $treeDataGenerator;

	/**
	 * @var CookieHandler
	 */
	protected $cookieHandler = null;

	/**
	 * @param RevisionStore $revisionStore
	 * @param TitleFactory $titleFactory
	 * @param ParserFactory $parserFactory
	 * @param TreeDataGenerator $treeDataGenerator
	 * @param CookieHandler $cookieHandler
	 * @param string $pagename
	 * @param string $id
	 */
	public function __construct(
		RevisionStore $revisionStore, TitleFactory $titleFactory, ParserFactory $parserFactory,
		TreeDataGenerator $treeDataGenerator, CookieHandler $cookieHandler, string $pagename, string $id
	) {
		$this->logger = LoggerFactory::getInstance( 'bluespicediscovery' );
		$this->title = $titleFactory->newFromText( $pagename );
		$this->revisionStore = $revisionStore;
		$this->parserFactory = $parserFactory;
		$this->treeDataGenerator = $treeDataGenerator;
		$this->cookieHandler = $cookieHandler;
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'enhanced-mediawiki-sidebar';
	}

	/**
	 * @return Message
	 */
	public function getLabelMsg(): Message {
		return new Message( 'bs-discovery-menu-provider-enhanced-mediawiki-sidebar-tree-label' );
	}

	/**
	 * @return Message
	 */
	public function getDescriptionMsg(): Message {
		return new RawMessage( 'bs-discovery-menu-provider-enhanced-mediawiki-sidebar-tree-desc' );
	}

	/**
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		$sidebarComponent = new EnhancedSidebarContainer(
			$this->id,
			$this->title,
			$this->revisionStore,
			$this->parserFactory,
			$this->treeDataGenerator,
			$this->cookieHandler,
		);

		$sidebarComponent->setLogger( $this->logger );

		return $sidebarComponent;
	}
}
