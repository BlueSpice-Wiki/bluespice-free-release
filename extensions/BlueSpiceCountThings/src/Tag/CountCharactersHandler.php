<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use BlueSpice\RendererFactory;
use BsPageContentProvider;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Title\TitleFactory;
use Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class CountCharactersHandler implements ITagHandler {

	/**
	 * @param LinkRenderer $linkRenderer
	 * @param RendererFactory $rendererFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly LinkRenderer $linkRenderer,
		private readonly RendererFactory $rendererFactory,
		private readonly TitleFactory $titleFactory
	) {
	}

	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$return = '';
		$countAllData = $this->magicWordBsCountCharacters( $input, $params );
		foreach ( $countAllData as $title => $countData ) {
			$content = [];
			foreach ( $countData as $key => $value ) {
				$content[] = [
					'label' => Message::newFromKey( 'bs-countthings-' . $key . '-label' )->text(),
					'count' => $value
				];
			}

			$params = new Params( [
				'title' => $title,
				'titlelink' => $this->linkRenderer->makeLink(
					$this->titleFactory->newFromText( $title ),
				),
				Renderer::PARAM_CONTENT => $content
			] );
			$return .= $this->rendererFactory->get( 'countthings-countcharacters', $params )->render();
		}
		return $return;
	}

	/**
	 *
	 * @return array
	 */
	private function magicWordBsCountCharacters( string $input, array $params ) {
		$input = explode( ' ', trim( $input ) );
		$out = [];
		if (
			isset( $params['mode'] )
		) {
			$mode = $params['mode'];
			if (
				!str_contains( $mode, 'chars' ) &&
				!str_contains( $mode, 'words' ) &&
				!str_contains( $mode, 'pages' )
			) {
				$mode = 'all';
			}
		} else {
			$mode = 'all';
		}
		$titles = [];
		foreach ( $input as $title ) {
			$titleInstance = $this->titleFactory->newFromText( $title );
			if ( $titleInstance ) {
				$titles[$title] = $titleInstance;
			}
		}
		foreach ( $titles as $title => $titleInstance ) {
			$out[$title] = [];

			$content = preg_replace(
				"/\s+/", " ", BsPageContentProvider::getInstance()->
			getContentFromTitle( $titleInstance )
			);
			if ( str_contains( $mode, 'chars' ) || str_contains( $mode, 'all' ) ) {
				$out[$title]['chars'] = strlen( $content );
			}
			if ( str_contains( $mode, 'words' ) || str_contains( $mode, 'all' ) ) {
				$out[$title]['words'] = count( explode( ' ', trim( $content ) ) );
			}
			if ( str_contains( $mode, 'pages' ) || str_contains( $mode, 'all' ) ) {
				$out[$title]['pages'] = ceil( strlen( $content ) / 2000 );
			}
		}

		return $out;
	}
}
