<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\DropletSource;

use InvalidArgumentException;
use MediaWiki\Content\JsonContent;
use MediaWiki\Extension\ContentDroplets\Droplet\CustomTagDroplet;
use MediaWiki\Extension\ContentDroplets\Droplet\CustomTemplateDroplet;
use MediaWiki\Extension\ContentDroplets\IDropletSource;
use WikiPage;

class WikiPageSource implements IDropletSource {
	/** @var WikiPage */
	private $wikipage;

	/**
	 * @param WikiPage $wikiPage
	 */
	public function __construct( WikiPage $wikiPage ) {
		$this->wikipage = $wikiPage;
	}

	/**
	 * @inheritDoc
	 */
	public function getDroplets(): array {
		if ( !$this->wikipage->getTitle()->exists() ) {
			return [];
		}

		$pageTitle = $this->wikipage->getTitle()->getPrefixedText();
		$content = $this->wikipage->getContent();
		if ( !( $content instanceof JsonContent ) ) {
			throw new InvalidArgumentException(
				"Page \"$pageTitle\" must have JSON content model"
			);
		}
		$status = $content->getData();
		if ( !$status->isOK() ) {
			throw new InvalidArgumentException( "Invalid content on \"$pageTitle\"" );
		}

		$json = $status->getValue();
		return $this->makeDroplets( $json );
	}

	/**
	 * @param array $json
	 * @return array
	 */
	private function makeDroplets( object $json ): array {
		$droplets = [];
		foreach ( $json as $key => $data ) {
			if ( $data->type == 'tag' ) {
				// for tags specified in WikiPage
				$droplets[$key] = new CustomTagDroplet(
					$data->name,
					$data->description,
					$data->icon,
					$data->tagname,
					$this->encode( $data->attributes ),
					$data->hasContent,
					$data->veCommand,
					$data->type,
					$data->categories,
					$data->rlModules
				);
				continue;
			}
			$droplets[$key] = new CustomTemplateDroplet(
				$data->name,
				$data->description,
				$data->icon,
				$data->template,
				$this->encode( $data->params ),
				'template',
				$data->categories,
				$data->rlModules
			);
		}

		return $droplets;
	}

	/**
	 *
	 * @param array $params
	 * @return array
	 */
	private function encode( $params ) {
		$formatted = [];
		$params = json_decode( json_encode( $params ), true );
		foreach ( $params as $param ) {
			foreach ( $param as $key => $value ) {
				$formatted[ $key ] = $value;
			}
		}
		return $formatted;
	}
}
