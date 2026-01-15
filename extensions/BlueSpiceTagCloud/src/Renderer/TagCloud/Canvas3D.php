<?php

namespace BlueSpice\TagCloud\Renderer\TagCloud;

use BlueSpice\Renderer\Params;
use BlueSpice\Utility\CacheHelper;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Linker\LinkRenderer;

class Canvas3D extends \BlueSpice\TagCloud\Renderer {
	public const PARAM_CANVAS_ID_PREFIX = 'canvasidprefix';
	public const PARAM_CANVAS_ID = 'canvasid';
	public const PARAM_CANVAS_ID_TAGS = 'canvasidtags';

	/**
	 *
	 * @var array
	 */
	protected static $canvasIDs = [];

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper|null $cacheHelper
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

		$this->args[static::PARAM_TAG] = 'div';
		$this->args[static::PARAM_CANVAS_ID] = $params->get(
			static::PARAM_CANVAS_ID,
			$this->generateCanvasID( 'bs-tagcloud-canvas3d-' )
		);
		$this->args[static::PARAM_CANVAS_ID_TAGS]
			= $this->args[static::PARAM_CANVAS_ID] . '-tags';

		$this->args['style'] = '';
	}

	/**
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	protected function render_content( $val ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$val = parent::render_content( $val );
		foreach ( $val as &$entry ) {
			$entry[ static::PARAM_SHOW_COUNT ] = $this->args[
				static::PARAM_SHOW_COUNT
			];
		}

		return array_values( $val );
	}

	/**
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	protected function render_style( $val ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		foreach ( $this->makeTagStyles() as $key => $style ) {
			$val .= " $key:'$style';";
		}
		return $val;
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpiceTagCloud.Canvas3D";
	}

	/**
	 *
	 * @param string $prefix
	 * @return string
	 */
	protected function generateCanvasID( $prefix ) {
		if ( empty( static::$canvasIDs ) ) {
			static::$canvasIDs[0] = $prefix . (string)0;
			return static::$canvasIDs[0];
		}
		$id = count( static::$canvasIDs ) - 1;
		static::$canvasIDs[$id] = $prefix . (string)$id;
		return static::$canvasIDs[$id];
	}
}
