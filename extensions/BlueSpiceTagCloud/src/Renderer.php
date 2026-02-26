<?php

namespace BlueSpice\TagCloud;

use BlueSpice\Renderer\Params;
use BlueSpice\TagCloud\Data\TagCloud\Record;
use BlueSpice\TagCloud\Data\TagCloud\ResultSet;
use BlueSpice\Utility\CacheHelper;
use InvalidArgumentException;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MWStake\MediaWiki\Component\DataStore\ResultSet as DataStoreResultSet;

abstract class Renderer extends \BlueSpice\TemplateRenderer {
	public const PARAM_RENDERER = 'renderer';
	public const PARAM_STORE = 'store';

	public const PARAM_RESULT = 'result';
	public const PARAM_CONTEXT = 'context';

	public const PARAM_WIDTH = 'width';
	public const PARAM_SHOW_COUNT = 'showcount';
	public const PARAM_HEADLINE = 'title';

	public const PARAM_MINSIZE = 'minsize';
	public const PARAM_MAXSIZE = 'maxsize';

	/**
	 *
	 * @var ResultSet
	 */
	protected $result = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper|null $cacheHelper
	 * @throws InvalidArgumentException
	 */
	protected function __construct( Config $config, Params $params,
		?LinkRenderer $linkRenderer = null, ?IContextSource $context = null,
		$name = '', ?CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$result = $params->get(
			static::PARAM_RESULT,
			null
		);
		if ( !$result instanceof DataStoreResultSet ) {
			throw new InvalidArgumentException(
				__CLASS__ . ':' . __METHOD__ . ' - invalid "' . static::PARAM_RESULT
			);
		}
		$this->result = new ResultSet( $result );

		$this->context = $params->get(
			static::PARAM_CONTEXT,
			null
		);
		if ( !$this->context instanceof Context ) {
			throw new InvalidArgumentException(
				__CLASS__ . ':' . __METHOD__ . ' - invalid "' . static::PARAM_CONTEXT
			);
		}

		$this->args[static::PARAM_WIDTH] = $params->get(
			static::PARAM_WIDTH,
			"100%"
		);

		$showcountParam = $params->get(
			static::PARAM_SHOW_COUNT,
			true
		);
		if ( $showcountParam == false || $showcountParam === "false" ) {
			$this->args[static::PARAM_SHOW_COUNT] = false;
		} else {
			$this->args[static::PARAM_SHOW_COUNT] = true;
		}

		$this->args[static::PARAM_HEADLINE] = $params->get(
			static::PARAM_HEADLINE,
			''
		);

		$this->args[static::PARAM_RENDERER] = $params->get(
			static::PARAM_RENDERER,
			''
		);

		$this->args[static::PARAM_STORE] = $params->get(
			static::PARAM_STORE,
			''
		);

		$this->args[static::PARAM_MINSIZE] = $params->get(
			static::PARAM_MINSIZE,
			8
		);

		$this->args[static::PARAM_MAXSIZE] = $params->get(
			static::PARAM_MAXSIZE,
			24
		);

		$this->args[static::PARAM_TAG] = 'ul';
		$this->args[static::PARAM_CONTENT] = [];

		if ( !$this->args[static::PARAM_CLASS] ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS] .=
			" bs-tagcloud"
			. " {$this->args[static::PARAM_STORE]}"
			. " {$this->args[static::PARAM_RENDERER]}";
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpiceTagCloud.List";
	}

	/**
	 *
	 * @param int $count
	 * @param int $mincount
	 * @param int $maxcount
	 * @param int $minsize
	 * @param int $maxsize
	 * @param int $tresholds
	 * @return int
	 */
	protected function getTagSizeLogarithmic( $count, $mincount, $maxcount, $minsize,
		$maxsize, $tresholds = 0 ) {
		if ( !is_int( $tresholds ) || $tresholds < 2 ) {
			$tresholds = $maxsize - $minsize;
			$treshold = 1;
		} else {
			$treshold = ( $maxsize - $minsize ) / ( $tresholds - 1 );
		}
		$log = $tresholds * log( $count - $mincount + 2 )
			/ log( $maxcount - $mincount + 2 ) - 1;

		return round( $minsize + round( $log ) * $treshold );
	}

	/**
	 *
	 * @return array
	 */
	protected function makeTagAttribs() {
		$attribs = parent::makeTagAttribs();
		$attribs['style'] = '';
		foreach ( $this->makeTagStyles() as $key => $style ) {
			$attribs['style'] .= " $key:$style;";
		}
		return $attribs;
	}

	/**
	 *
	 * @param array $styles
	 * @return array
	 */
	protected function makeTagStyles( $styles = [] ) {
		$styles[static::PARAM_WIDTH] = $this->args[static::PARAM_WIDTH];
		return $styles;
	}

	/**
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	protected function render_content( $val ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		foreach ( $this->result->getRecords() as $record ) {
			$data = array_filter( (array)$record->getData(), static function ( $e ) {
				return !empty( $e );
			} );
			$data['weight'] = $this->getTagSizeLogarithmic(
				$record->get( Record::COUNT, 0 ),
				$this->result->getLowestCount(),
				$this->result->getHighestCount(),
				$this->args[static::PARAM_MINSIZE],
				$this->args[static::PARAM_MAXSIZE]
			);
			$val[] = $data;
		}
		return $val;
	}

}
