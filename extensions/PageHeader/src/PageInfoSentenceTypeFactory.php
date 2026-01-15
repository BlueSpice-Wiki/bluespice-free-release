<?php

namespace PageHeader;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\ManifestRegistry\IRegistry;
use PageHeader\PageInfoSentence\IType;

class PageInfoSentenceTypeFactory {

	/**
	 *
	 * @var IRegistry
	 */
	private $registry = null;

	/**
	 *
	 * @var Config
	 */
	private $config = null;

	/**
	 *
	 * @param IRegistry $registry
	 * @param Config $config
	 */
	public function __construct( IRegistry $registry, Config $config ) {
		$this->registry = $registry;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $key
	 * @param IContextSource $context
	 * @return IType
	 */
	public function get( $key, IContextSource $context ): IType {
		$nullCallable = "\\PageHeader\\PageInfoSentence\\Type\\NullType::factory";
		$callback = $this->registry->getValue( $key, $nullCallable );
		if ( !is_callable( $callback ) ) {
			return call_user_func_array(
				$nullCallable,
				[ $context, $this->config ]
			);
		}
		return call_user_func_array(
			$callback,
			[ $context, $this->config ]
		);
	}
}
