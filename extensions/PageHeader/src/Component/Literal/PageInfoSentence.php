<?php

namespace PageHeader\Component\Literal;

use HTML;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use PageHeader\PageInfoSentenceBuilder;

class PageInfoSentence extends Literal {
	/**
	 *
	 * @var PageInfoSentenceBuilder
	 */
	protected $builder = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var string
	 */
	protected $sentence = '';

	/**
	 *
	 * @param PageInfoSentenceBuilder $builder
	 */
	public function __construct( $builder ) {
		parent::__construct(
			'pageheader-pageinfosentence-component',
			''
		);
		$this->builder = $builder;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$this->context = $context;
		if ( !$this->context->getTitle() || !$this->context->getTitle()->exists() ) {
			return false;
		}
		if ( $this->context->getTitle()->isTalkPage() ) {
			return false;
		}
		if ( $this->context->getRequest()->getVal( 'action', 'view' ) === 'history' ) {
			return false;
		}
		if ( $this->context->getRequest()->getVal( 'diff', '-1' ) !== '-1' ) {
			return false;
		}
		if ( $this->getSentence()
			=== $this->builder->makeDefaultPageInfoSentence( $context ) ) {
			// TODO: instead of dirty string comparison of the default message
			// have a dedicated has items/elements method in the builder!
			return false;
		}
		return true;
	}

	/**
	 * @return string
	 */
	protected function getSentence(): string {
		if ( !empty( $this->sentence ) ) {
			return $this->sentence;
		}
		$this->sentence = $this->builder->build( $this->context );
		return $this->sentence;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml(): string {
		return HTML::rawElement( 'span', [ 'role' => 'alert' ], $this->getSentence() );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [ 'ext.pageheader.styles' ];
	}
}
