<?php

namespace BlueSpice\TagCloud\Renderer\TagCloud;

class LinkList extends \BlueSpice\TagCloud\Renderer {

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

}
