<?php

namespace BlueSpice\WatchList\UserSidebar\Widget;

use BlueSpice\UserSidebar\IWidget;
use BlueSpice\UserSidebar\Widget;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\SelectQueryBuilder;

class WatchList extends Widget {

	private const COUNT = 5;

	/** @var IConnectionProvider */
	private $connectionProvider;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param string $key
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array $params
	 * @return IWidget|static
	 */
	public static function factory(
		string $key, IContextSource $context, Config $config, array $params = []
	) {
		$services = MediaWikiServices::getInstance();
		return new static(
			$key, $context, $config, $params,
			$services->getConnectionProvider(), $services->getTitleFactory()
		);
	}

	public function __construct(
		string $key, IContextSource $context, Config $config, array $params,
		IConnectionProvider $lb, TitleFactory $titleFactory
	) {
		parent::__construct( $key, $context, $config, $params );
		$this->connectionProvider = $lb;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @return Message
	 */
	public function getHeaderMessage(): Message {
		return $this->context->msg( 'bs-watchlist-usersidebar-title' );
	}

	/**
	 * @return array
	 */
	public function getLinks(): array {
		$user = $this->context->getUser();
		if ( $user->isAnon() ) {
			return [];
		}

		$watchlistTitles = $this->getWatchlistTitles( $user );

		$links = [];
		foreach ( $watchlistTitles as $title ) {
			$link = [
				'href' => $title['title']->getLocalURL(),
				'text' => $title['displayText'],
				'title' => $title['displayText'],
				'classes' => ' bs-usersidebar-internal '
			];
			$links[] = $link;
		}

		return $links;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	private function getWatchlistTitles( User $user ) {
		$dbr = $this->connectionProvider->getReplicaDatabase();
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_namespace', 'page_title' ] )
			->table( 'watchlist' )
			->join( 'page', null, [
				'page_namespace = wl_namespace',
				'page_title = wl_title'
			] )
			->where( [
				'wl_user' => $user->getId(),
				'wl_notificationtimestamp IS NOT NULL',
				'page_content_model' => 'wikitext'
			] )
			->orderBy( 'wl_notificationtimestamp', SelectQueryBuilder::SORT_DESC )
			->limit( static::COUNT )
			->fetchResultSet();

		$watchlist = [];
		foreach ( $res as $row ) {
			$title = $this->titleFactory->newFromRow( $row );

			$watchlist[] = [
				'title' => $title,
				'displayText' => $title->getPrefixedText()
			];
		}

		return $watchlist;
	}
}
