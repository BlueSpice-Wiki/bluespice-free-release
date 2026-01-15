<?php

namespace BlueSpice\PagesVisited\UserSidebar\Widget;

use BlueSpice\PagesVisited\Data\Store;
use BlueSpice\UserSidebar\Widget;
use BlueSpice\WhoIsOnline\Data\Record;
use BsStringHelper;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\Filter\ListValue;
use MWStake\MediaWiki\Component\DataStore\Filter\Numeric;
use MWStake\MediaWiki\Component\DataStore\Filter\StringValue;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class PagesVisited extends Widget {
	public const PARAM_TITLE_MAX_LENGHT = 'maxtitlelength';
	public const PARAM_NAMESPACES = 'namespaces';
	public const PARAM_COUNT = 'count';

	/**
	 *
	 * @return bool
	 */
	public function shouldRender(): bool {
		return $this->context->getUser()->isAnon();
	}

	/**
	 *
	 * @return Message
	 */
	public function getHeaderMessage(): Message {
		return $this->context->msg( 'bs-pagesvisited-widget-title' );
	}

	/**
	 *
	 * @return array
	 */
	public function getLinks(): array {
		if ( $this->context->getUser()->isAnon() ) {
			return [];
		}
		$readerParams = new ReaderParams( $this->makeReaderParams() );
		$recordSet = ( new Store() )->getReader()->read( $readerParams );

		// Dumb default
		$maxTitleLength = 20;
		if ( isset( $this->params[static::PARAM_TITLE_MAX_LENGHT] ) ) {
			$maxTitleLength = (int)$this->params[static::PARAM_TITLE_MAX_LENGHT];
		}

		$links = [];
		foreach ( $recordSet->getRecords() as $record ) {
			$title = Title::makeTitle(
				$record->get( Record::PAGE_NAMESPACE ),
				$record->get( Record::PAGE_TITLE )
			);
			if ( !$title || !$title->isKnown() ) {
				continue;
			}
			$display = BsStringHelper::shorten( $title->getPrefixedText(), [
				'max-length' => $maxTitleLength,
				'position' => 'middle'
			] );
			$link = [
				'href' => $title->getLocalURL(),
				'text' => $display,
				'classes' => ' bs-usersidebar-internal ',
				'aria' => [
					'label' => $title->getPrefixedText()
				]
			];
			$links[] = $link;
		}
		return $links;
	}

	/**
	 *
	 * @return array
	 */
	protected function makeReaderParams() {
		$params = [
			ReaderParams::PARAM_LIMIT => 7,
			ReaderParams::PARAM_FILTER => [],
			ReaderParams::PARAM_FILTER => [ [
				Filter::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::ACTION,
				Filter::KEY_VALUE => 'view',
				Filter::KEY_TYPE => FieldType::STRING
			], [ Filter::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::USER_ID,
				Filter::KEY_VALUE => (int)$this->context->getUser()->getId(),
				Filter::KEY_TYPE => 'numeric'
			] ]
		];
		if ( !empty( $this->params[static::PARAM_NAMESPACES] ) ) {
			$params[ReaderParams::PARAM_FILTER][] = [
				Filter::KEY_COMPARISON => ListValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::PAGE_NAMESPACE,
				Filter::KEY_VALUE => static::PARAM_NAMESPACES,
				Filter::KEY_TYPE => FieldType::LISTVALUE
			];
		}
		if ( !empty( $this->params[static::PARAM_COUNT] ) ) {
			$params[ReaderParams::PARAM_LIMIT] = $this->params[static::PARAM_COUNT];
		}

		return $params;
	}

}
