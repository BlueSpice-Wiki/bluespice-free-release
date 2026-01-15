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
use Wikimedia\ObjectCache\WANObjectCache;

class EnhancedSidebar implements IMenuProvider {

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @param RevisionStore $revisionStore
	 * @param TitleFactory $titleFactory
	 * @param ParserFactory $parserFactory
	 * @param TreeDataGenerator $treeDataGenerator
	 * @param CookieHandler|null $cookieHandler
	 * @param WANObjectCache $objectCache
	 * @param string $pagename
	 * @param string $id
	 */
	public function __construct(
		private readonly RevisionStore $revisionStore,
		TitleFactory $titleFactory,
		private readonly ParserFactory $parserFactory,
		private readonly TreeDataGenerator $treeDataGenerator,
		private readonly ?CookieHandler $cookieHandler = null,
		private readonly WANObjectCache $objectCache,
		string $pagename,
		private readonly string $id = ''
	) {
		$this->logger = LoggerFactory::getInstance( 'bluespicediscovery' );
		$this->title = $titleFactory->newFromText( $pagename );
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
			$this->objectCache
		);

		$sidebarComponent->setLogger( $this->logger );

		return $sidebarComponent;
	}
}
