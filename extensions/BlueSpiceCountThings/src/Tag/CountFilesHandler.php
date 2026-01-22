<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use Wikimedia\Rdbms\ILoadBalancer;

class CountFilesHandler implements ITagHandler {

	/**
	 * @param ILoadBalancer $lb
	 */
	public function __construct(
		private readonly ILoadBalancer $lb
	) {
	}

	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$distinct = 'DISTINCT';
		if ( $params['noduplicates'] ) {
			$distinct = '';
		}
		$number = $this->lb->getConnection( DB_REPLICA )->newSelectQueryBuilder()
			->from( 'image' )
			->select( 'COUNT( ' . $distinct . ' img_sha1 )' )
			->caller( __METHOD__ )
			->fetchField();
		return " $number ";
	}
}
