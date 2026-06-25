<?php

namespace MediaWiki\Extension\MenuEditor\Api;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\MenuEditor\MenuFactory;
use MediaWiki\Extension\MenuEditor\Parser\WikitextMenuParser;
use MediaWiki\Rest\HttpException;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;
use Wikimedia\ParamValidator\ParamValidator;

class AppendNodeHandler extends MenuHandler {

	public function __construct(
		TitleFactory $titleFactory,
		MenuFactory $menuFactory,
		ParserFactory $parserFactory,
		private readonly RevisionLookup $revisionLookup
	) {
		parent::__construct( $titleFactory, $menuFactory, $parserFactory );
	}

	/**
	 * @return \MediaWiki\Rest\Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$page = $this->makeTitle( $params['pagename'] );
		$body = $this->getValidatedBody();

		$revision = $this->revisionLookup->getRevisionByTitle( $page );
		$parser = $this->getParserForRevision( $page, $revision );
		if ( !( $parser instanceof WikitextMenuParser ) ) {
			throw new HttpException( 'invalid-menu-type' );
		}

		$data = $body['data'] ?? [];
		$insertAfter = $data['after'] ?? null;
		$nodes = $data['nodes'] ?? [];
		foreach ( $nodes as $node ) {
			$nodeObject = $parser->getNodeFromData( $node );
			if ( !$nodeObject ) {
				continue;
			}
			$parser->addNodeAfter( $nodeObject, $insertAfter );
		}

		$rev = $parser->saveRevision( RequestContext::getMain()->getUser() );
		if ( !$rev ) {
			throw new HttpException( 'save-failed' );
		}
		return $this->getResponseFactory()->createJson( [
			'revision' => $rev->getId(),
		] );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'pagename' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string',
			]
		];
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'data' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => ''
			]
		];
	}
}
