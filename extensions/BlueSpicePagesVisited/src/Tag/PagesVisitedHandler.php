<?php

namespace BlueSpice\PagesVisited\Tag;

use BlueSpice\PagesVisited\Data\Store;
use BlueSpice\WhoIsOnline\Data\Record;
use BsStringHelper;
use HtmlArmor;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\Filter\ListValue;
use MWStake\MediaWiki\Component\DataStore\Filter\NumericValue;
use MWStake\MediaWiki\Component\DataStore\Filter\StringValue;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Sort;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class PagesVisitedHandler implements ITagHandler {

	/**
	 * @param TitleFactory $titleFactory
	 * @param LinkRenderer $linkRenderer
	 */
	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly LinkRenderer $linkRenderer
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$context = RequestContext::getMain();
		if ( !$parser->getUserIdentity()->isRegistered() ) {
			return $context->msg( 'bs-pagesvisited-label-anon-user' )->text();
		}

		$readerParams = new ReaderParams( $this->makeParams( $params, $parser->getUserIdentity() ) );
		$recordSet = ( new Store() )->getReader()->read( $readerParams );
		$maxTitleLength = $params['maxtitlelength'];

		$out = Html::openElement( 'ul' );
		if ( $recordSet->getTotal() > 0 ) {
			foreach ( $recordSet->getRecords() as $record ) {
				$title = $this->titleFactory->makeTitleSafe(
					$record->get( Record::PAGE_NAMESPACE ),
					$record->get( Record::PAGE_TITLE )
				);
				if ( !$title ) {
					continue;
				}
				$display = null;
				if ( $maxTitleLength > 0 ) {
					$display = new HtmlArmor( BsStringHelper::shorten( $title->getPrefixedText(), [
						'max-length' => $maxTitleLength,
						'position' => 'middle'
					] ) );
				}
				$out .= Html::openElement( 'li' );
				$out .= $this->linkRenderer->makeLink( $title, $display );
				$out .= Html::closeElement( 'li' );
			}
		}
		$out .= Html::closeElement( 'ul' );
		return $out;
	}

	/**
	 *
	 * @return array
	 */
	protected function makeParams( array $params, UserIdentity $userIdentity ) {
		$params = [
			ReaderParams::PARAM_LIMIT => $params['count'] ?? ReaderParams::LIMIT_INFINITE,
			ReaderParams::PARAM_FILTER => [],
			ReaderParams::PARAM_FILTER => [ [
				Filter::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::ACTION,
				Filter::KEY_VALUE => 'view',
				Filter::KEY_TYPE => FieldType::STRING
			], [ Filter::KEY_COMPARISON => NumericValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::USER_ID,
				Filter::KEY_VALUE => $userIdentity->getId(),
				Filter::KEY_TYPE => 'numeric'
			] ]
		];
		if ( !empty( $params['namespaces'] ) ) {
			$params[ReaderParams::PARAM_FILTER][] = [
				Filter::KEY_COMPARISON => ListValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::PAGE_NAMESPACE,
				Filter::KEY_VALUE => $params['namespaces'],
				Filter::KEY_TYPE => FieldType::LISTVALUE
			];
		}
		if ( $params['order'] === 'pagename' ) {
			$params[ReaderParams::PARAM_SORT][] = [
				'property' => Record::PAGE_TITLE,
				'direction' => Sort::ASCENDING,
			];
		}
		return $params;
	}
}
