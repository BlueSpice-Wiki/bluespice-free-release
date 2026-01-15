<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\ITitleActionPrimaryActionModifier;
use Exception;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Registration\ExtensionRegistry;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIconSplitButton;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownItemlistFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;
use Wikimedia\ObjectFactory\ObjectFactory;

class TitleActionEdit extends SimpleDropdownIconSplitButton {

	/**
	 *
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 *
	 * @var array
	 */
	private $editActions = [];

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param array $componentProcessData
	 */
	public function __construct( $permissionManager, $componentProcessData, $objectFactory ) {
		parent::__construct( [] );

		$this->permissionManager = $permissionManager;
		$this->objectFactory = $objectFactory;

		if ( isset( $componentProcessData['panel'] )
			&& isset( $componentProcessData['panel']['edit'] ) ) {
				$this->editActions = $componentProcessData['panel'][ 'edit' ];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'primary-' . $this->getPrimaryActionId();
	}

	/**
	 *
	 * @return string
	 */
	private function getPrimaryActionId() {
		$actionIds = array_keys( $this->editActions );

		if ( empty( $actionIds ) ) {
			return 'ta-edit-btn';
		}

		$primaryAction = '';
		if ( isset( $this->editActions['ca-edit'] ) ) {
			$primaryAction = 'ca-edit';
		}

		if ( isset( $this->editActions['ca-ve-edit'] ) ) {
			$primaryAction = 'ca-ve-edit';
		}

		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoveryTitleActionPrimaryActionModifier'
		);

		if ( !empty( $registry ) ) {
			ksort( $registry );
			$spec = array_pop( $registry );

			$modifier = $this->objectFactory->createObject( $spec, [ $actionIds, $primaryAction ] );
			if ( $modifier instanceof ITitleActionPrimaryActionModifier ) {
				$modifiedActionId = $modifier->getActionId( $actionIds, $primaryAction );
				if ( in_array( $modifiedActionId, $actionIds ) ) {
					$primaryAction = $modifiedActionId;
				}
			}
		}

		if ( $primaryAction === '' ) {
			return $actionIds[0];
		} else {
			return $primaryAction;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		$actionKey = $this->getPrimaryActionId();

		if ( isset( $this->editActions[$actionKey]['href'] ) ) {
			return $this->editActions[$actionKey]['href'];
		} else {
			throw new Exception( 'No edit action found' );
		}
	}

	/**
	 * @return array
	 */
	public function getContainerClasses(): array {
		return $this->options['container-classes'];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		$classes = [ 'ico-btn' ];
		$actions = $this->getEditActions();
		if ( empty( $actions ) ) {
			array_push( $classes, 'disabled' );
		}
		return $classes;
	}

	/**
	 * @return array
	 */
	public function getSplitButtonClasses(): array {
		return [ 'dropdown-icon' ];
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return [ 'mws-dropdown-secondary', 'dropdown-menu-end' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses(): array {
		return [ 'bi-pencil-fill' ];
	}

	/**
	 * @return Message
	 */
	public function getButtonTitle(): Message {
		return Message::newFromKey( 'bs-discovery-title-action-edit-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getSplitButtonTitle(): Message {
		return Message::newFromKey( 'bs-discovery-title-action-edit-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getButtonAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-title-action-edit-button-aria-label' );
	}

	/**
	 * @return Message
	 */
	public function getSplitButtonAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-title-action-edit-button-aria-label' );
	}

	/**
	 *
	 * @return bool
	 */
	public function splitButtonIsDisabled(): bool {
		$editActions = $this->getEditActions();
		if ( empty( $editActions ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$editActions = $this->getEditActions();
		if ( empty( $editActions ) ) {
			return [];
		}
		return [
			new SimpleDropdownItemlistFromArray( [
				'id' => 'new-content-itms',
				'classes' => [],
				'links' => $this->getEditActions()
			] )
		];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		if ( empty( $this->editActions ) ) {
			return false;
		}

		$user = $context->getUser();
		$title = $context->getTitle();
		$userCan = $this->permissionManager->quickUserCan( 'edit', $user, $title );
		if ( $userCan ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return array
	 */
	private function getEditActions(): array {
		if ( empty( $this->editActions ) ) {
			return [];
		}

		$primaryActionId = $this->getPrimaryActionId();
		$actions = $this->editActions;
		// Set primary action on first place
		if ( isset( $actions[$primaryActionId] ) ) {
			$newActions = $actions[$primaryActionId];
			unset( $actions[$primaryActionId] );
			array_unshift( $actions, $newActions );
		}

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		return $linkFormatter->formatLinks( $actions );
	}
}
