<?php

namespace MediaWiki\Extension\FlexiSkin\Api;

use MediaWiki\Api\ApiUsageException;
use MediaWiki\Extension\FlexiSkin\FlexiSkin;
use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use Wikimedia\ParamValidator\ParamValidator;

abstract class FlexiSkinOperation extends FlexiSkinApiBase {

	/** @inheritDoc */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + [
			'skinname' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
				static::PARAM_HELP_MSG => 'apihelp-flexiskin-skinoperation-param-skinname',
			]
		];
	}

	/**
	 * @throws ApiUsageException
	 */
	public function execute() {
		$this->checkPermissions();
		$result = $this->executeAction();
		$this->returnResult( $result );
	}

	/**
	 * @param bool $value
	 */
	protected function returnResult( $value ) {
		$result = $this->getResult();
		$result->addValue( null, 'success', (int)$value );
	}

	/**
	 *
	 * @return bool
	 * @throws ApiUsageException
	 */
	protected function executeAction() {
		$flexiSkin = $this->getFlexiSkin();
		return $this->executeOperationOnSkin( $flexiSkin );
	}

	/**
	 * @return IFlexiSkin
	 * @throws ApiUsageException
	 */
	protected function getFlexiSkin() {
		$skinName = $this->getParameter( 'skinname' );
		$skin = $this->flexiSkinManager->getFlexiSkin( $skinName );
		if ( $skin === null ) {
			if ( $this->mustExist() ) {
				$this->dieWithError( 'Skin must exist!' );
			}
			return $this->flexiSkinManager->create( $skinName, [] );
		}

		return $skin;
	}

	/**
	 * Get a new skin with different config
	 *
	 * @param IFlexiSkin $skin
	 * @param array $config
	 * @return FlexiSkin
	 */
	protected function replaceConfig( IFlexiSkin $skin, array $config ) {
		return new FlexiSkin(
			$skin->getId(),
			$skin->getName(),
			$config,
			$skin->isActive()
		);
	}

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return bool Operation success
	 */
	abstract protected function executeOperationOnSkin( IFlexiSkin $flexiSkin );

	/**
	 * @return bool
	 */
	abstract protected function mustExist(): bool;
}
