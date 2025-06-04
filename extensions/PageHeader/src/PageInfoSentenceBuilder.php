<?php

namespace PageHeader;

use MediaWiki\Context\IContextSource;
use MediaWiki\Html\Html;

class PageInfoSentenceBuilder {

	/**
	 * @var PageInfoFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @var PageInfoSentenceTypeFactory
	 */
	protected $typeFactory = null;

	/**
	 * @param PageInfoFactory $factory
	 * @param PageInfoSentenceTypeFactory $typeFactory
	 */
	public function __construct( PageInfoFactory $factory,
		PageInfoSentenceTypeFactory $typeFactory ) {
		$this->factory = $factory;
		$this->typeFactory = $typeFactory;
	}

	/**
	 * @param IContextSource $context
	 * @return string
	 */
	public function build( IContextSource $context ) {
		$items = array_filter(
			$this->factory->getAll( $context ),
			static function ( IPageInfo $e ) use ( $context ) {
				return $e->shouldShow( $context );
			} );
		usort( $items, static function ( IPageInfo $a, IPageInfo $b ) {
			if ( $a->getPosition() === $b->getPosition() ) {
				return 0;
			}
			return $a->getPosition() < $b->getPosition() ? -1 : 1;
		} );
		$pro = array_filter( $items, static function ( IPageInfo $e ) {
			return $e->getItemClass() === IPageInfo::ITEMCLASS_PRO;
		} );
		$contra = array_filter( $items, static function ( IPageInfo $e ) {
			return $e->getItemClass() === IPageInfo::ITEMCLASS_CONTRA;
		} );
		if ( empty( $pro ) && empty( $contra ) ) {
			return $this->makeDefaultPageInfoSentence( $context );
		}
		return $this->makePagInfoSentence( $context, $pro, $contra );
	}

	/**
	 *
	 * @param IPageInfo[] $items
	 * @param IContextSource $context
	 * @return array
	 */
	private function getSentenceParts( $items, IContextSource $context ) {
		$parts = [];

		foreach ( $items as $item ) {
			$parts[] = $this->typeFactory->get( $item->getType(), $context )->build( $item );
		}

		return $parts;
	}

	/**
	 * @param IContextSource $context
	 * @param array $pro
	 * @param array $contra
	 * @return string
	 */
	private function makePagInfoSentence( IContextSource $context, $pro, $contra ) {
		$numPro = count( $pro );
		$numCon = count( $contra );

		$sentence = '';
		if ( $numCon > 2 ) {
			$lastItem = array_pop( $contra );
			$parts = $this->getSentenceParts( $contra, $context );
			$separator = $context->msg(
				'pageheader-pageinfosentence-item-separator-greater-two'
			)->plain();
			$separator .= ' ';
			$sentence .= implode( $separator, $parts );

			$sentence .= ' ';
			$sentence .= $context->msg(
				'pageheader-pageinfosentence-item-separator'
			)->plain();
			$sentence .= ' ';
			$sentence .= $lastItem->getLabelMessage()->text();
		} elseif ( $numCon > 0 ) {
			$parts = $this->getSentenceParts( $contra, $context );
			$separator = ' ';
			$separator .= $context->msg(
				'pageheader-pageinfosentence-item-separator'
			)->plain();
			$separator .= ' ';
			$sentence .= implode( $separator, $parts );
		}

		if ( $numCon > 0 && $numPro > 0 ) {
			$sentence .= ' ';
			$sentence .= $context->msg(
				'pageheader-pageinfosentence-item-class-separator'
			)->plain();
			$sentence .= ' ';
		}

		if ( $numPro > 2 ) {
			$lastItem = array_pop( $pro );
			$parts = $this->getSentenceParts( $pro, $context );
			$separator = $context->msg(
				'pageheader-pageinfosentence-item-separator-greater-two'
			)->plain();
			$separator .= ' ';
			$sentence .= implode( $separator, $parts );

			$sentence .= ' ';
			$sentence .= $context->msg(
				'pageheader-pageinfosentence-item-separator'
			)->plain();
			$sentence .= ' ';
			$sentence .= $lastItem->getLabelMessage()->text();
		} elseif ( $numPro > 0 ) {
			$parts = $this->getSentenceParts( $pro, $context );
			$separator = ' ';
			$separator .= $context->msg(
				'pageheader-pageinfosentence-item-separator'
			)->plain();
			$separator .= ' ';
			$sentence .= implode( $separator, $parts );
		}

		return $context->msg( 'pageheader-pageinfosentence', $sentence )->text();
	}

	/**
	 * @param IContextSource $context
	 * @return string
	 */
	public function makeDefaultPageInfoSentence( IContextSource $context ) {
		return Html::element(
			'span',
			[
				'title' => $context->msg( 'pageheader-pageinfosentence-no-item-tooltip' )->plain(),
				'class' => 'pageinfosentence-no-status'
			],
			$context->msg( 'pageheader-pageinfosentence-no-item-label' )->plain()
		);
	}
}
