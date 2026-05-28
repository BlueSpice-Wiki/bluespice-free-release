<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler;

use Exception;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\InputProcessor\Runner as InputProcessor;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use StatusValue;

class TagRenderer {
	public function __construct(
		private readonly ParserFactory $parserFactory,
		private readonly InputProcessor $inputProcessor,
		private readonly ITag $tag,
		private readonly MediaWikiServices $services
	) {
	}

	/**
	 * @param string|null $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return array|string
	 * @throws Exception
	 */
	public function doRender( ?string $input, array $args, Parser $parser, PPFrame $frame ): array|string {
		$tagHandler = $this->tag->getHandler( $this->services );
		$processedInput = $this->processInput( $input, $parser, $frame );
		$status = $this->validateParams( $args );
		if ( $status->isGood() ) {
			$processedParams = $status->getValue();
		} else {
			$errors = $status->getMessages( 'error' );
			$errorMessages = [];
			foreach ( $errors as $error ) {
				$msg = Message::newFromSpecifier( $error );
				$errorMessages[] = $msg->text();
			}
			return $this->showErrors( $errorMessages, $parser );
		}

		$content = $tagHandler->getRenderedContent( $processedInput, $processedParams, $parser, $frame );
		$modules = $this->tag->getResourceLoaderModules();
		if ( is_array( $modules ) ) {
			$parser->getOutput()->addModules( $modules );
		}

		$wrapperTag = $this->tag->getContainerElementName();
		if ( $wrapperTag ) {
			$content = Html::rawElement( $wrapperTag, $this->makeWrapperAttributes( $input ?? '', $args ), $content );
		}
		return [
			$content,
			MarkerType::KEY => (string)$this->tag->getMarkerType()
		];
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Title $title
	 * @param UserIdentity|null $forUser
	 * @return array|string
	 */
	public function render( string $input, array $args, Title $title, ?UserIdentity $forUser = null ) {
		$parser = $this->parserFactory->create();
		$parser->setPage( $title );
		if ( $forUser ) {
			$parser->setUser( $forUser );
			$parser->setOptions( ParserOptions::newFromUser( $forUser ) );
		} else {
			$parser->setOptions( ParserOptions::newFromAnon() );
		}
		$parser->resetOutput();
		return $this->doRender( $input, $args, $parser, $parser->getPreprocessor()->newFrame() );
	}

	/**
	 * @param string|null $input
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	private function processInput( ?string $input, Parser $parser, PPFrame $frame ): string {
		if ( !$input || !$this->tag->hasContent() ) {
			return '';
		}
		if ( $this->tag->shouldParseInput() ) {
			return $parser->recursiveTagParse( $input, $frame );
		}

		return $input;
	}

	/**
	 * @param array $args
	 * @return StatusValue
	 * @throws Exception
	 */
	private function validateParams( array $args ): StatusValue {
		$processors = $this->tag->getParamDefinition();
		if ( !$processors ) {
			return StatusValue::newGood( [] );
		}
		return $this->inputProcessor->process( $processors, $args );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @return array
	 */
	private function makeWrapperAttributes( string $input, array $args ) {
		$cssClasses = [ 'mwstake-components-generictaghandler-tag' ];
		foreach ( $this->tag->getTagNames() as $tagName ) {
			$cssClasses[] = Sanitizer::escapeClass( "mwstake-components-generictaghandler-tag-$tagName" );
		}

		$attribs = [
			'class' => implode( ' ', array_unique( $cssClasses ) )
		];

		foreach ( $args as $argName => $argValue ) {
			$attribs["data-mwstake-components-generictaghandler-arg-$argName"] = $argValue;
		}

		$attribs["data-mwstake-components-generictaghandler-input"] = $input;

		return $attribs;
	}

	/**
	 * @param array $messages
	 * @param Parser $parser
	 * @return string
	 */
	private function showErrors( array $messages, Parser $parser ): string {
		\OutputPage::setupOOUI();
		$parser->getOutput()->setEnableOOUI( true );
		$errorMsg = $parser->msg( 'generictaghandler-error', $this->tag->getTagNames()[0] );
		$html = Html::openElement( 'div' );
		$html .= Html::element( 'span', [], $errorMsg->text() );
		$html .= Html::openElement( 'ul' );
		foreach ( $messages as $message ) {
			$html .= Html::element( 'li', [], $message );
		}
		$html .= Html::closeElement( 'ul' );
		$html .= Html::closeElement( 'div' );

		$message = new MessageWidget( [
			'label' => new HtmlSnippet( $html ),
			'type' => 'error'
		] );
		return $message->toString();
	}
}
