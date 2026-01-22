<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\Rest;

use MediaWiki\Config\Config;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Context\DerivativeContext;
use MediaWiki\Context\RequestContext;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\MainConfigNames;
use MediaWiki\Preferences\DefaultPreferencesFactory;
use MediaWiki\Preferences\PreferencesFactory;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\Options\UserOptionsManager;
use PreferencesFormOOUI;
use Wikimedia\ParamValidator\ParamValidator;

class SavePreferences extends SimpleHandler {

	private UserOptionsManager $userOptionsManager;

	private PreferencesFactory $preferencesFactory;

	private SpecialPageFactory $specialPageFactory;

	private Config $config;

	/**
	 * @param UserOptionsManager $userOptionsManager
	 * @param PreferencesFactory $preferencesFactory
	 * @param SpecialPageFactory $specialPageFactory
	 * @param Config $config
	 */
	public function __construct(
		UserOptionsManager $userOptionsManager, PreferencesFactory $preferencesFactory,
		SpecialPageFactory $specialPageFactory, Config $config ) {
		$this->userOptionsManager = $userOptionsManager;
		$this->preferencesFactory = $preferencesFactory;
		$this->specialPageFactory = $specialPageFactory;
		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$context = RequestContext::getMain();
		$user = $context->getUser();
		$serviceOptions = new ServiceOptions(
			DefaultPreferencesFactory::CONSTRUCTOR_OPTIONS,
			$this->config
		);

		if ( $user->isAnon() ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'Preferences not for anon user' ] );
		}

		if ( !$user->isAllowedAny( 'editmyprivateinfo', 'editmyoptions' ) ) {
			return $this->getResponseFactory()->createHttpError( 404, [ wfMessage( 'mypreferencesprotected' ) ] );
		}

		$body = $this->getValidatedBody();
		$options = $body['options'];
		if ( !$options ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'No valid options' ] );
		}
		// Necessary to give context of a page to allow creation of form to get
		// validation for each preference depending on the input field
		$pageContext = new DerivativeContext( $context );
		$specialPage = $this->specialPageFactory->getPage( 'EnhancedPreferences' );
		$output = $specialPage->getOutput();
		$output->setTitle( $specialPage->getPageTitle() );
		$pageContext->setTitle( $specialPage->getPageTitle() );
		$pageContext->setOutput( $specialPage->getOutput() );
		$preferences = $this->preferencesFactory->getFormDescriptor( $user, $pageContext );
		$form = $this->preferencesFactory->getForm( $user, $pageContext, PreferencesFormOOUI::class );

		$hiddenPrefs = $serviceOptions->get( MainConfigNames::HiddenPrefs );
		$saveOptions = $options;
		if ( !in_array( 'realname', $hiddenPrefs )
			&& $user->isAllowed( 'editmyprivateinfo' )
			&& array_key_exists( 'realname', $options )
		) {
			$realName = $options['realname'];
			$user->setRealName( $realName );
		}

		if ( $user->isAllowed( 'editmyoptions' ) ) {
			foreach ( $this->getSaveBlacklist() as $b ) {
				unset( $options[$b] );
			}

			// If users have saved a value for a preference which has subsequently been disabled
			// via $wgHiddenPrefs, we don't want to destroy that setting in case the preference
			// is subsequently re-enabled
			foreach ( $hiddenPrefs as $pref ) {
				// If the user has not set a non-default value here, the default will be returned
				// and subsequently discarded
				$options[$pref] = $this->userOptionsManager->getOption( $user, $pref, null, true );
			}

			// If the user changed the rclimit preference, also change the rcfilters-rclimit preference
			if (
				isset( $options['rclimit'] ) &&
				intval( $options[ 'rclimit' ] ) !== $this->userOptionsManager->getIntOption( $user, 'rclimit' )
			) {
				$options['rcfilters-limit'] = $options['rclimit'];
			}

			$wrongValidated = [];
			foreach ( $options as $key => $value ) {
				// If we're creating a new local override, we need to explicitly pass
				// GLOBAL_OVERRIDE to setOption(), otherwise the update would be ignored
				// due to the conflicting global option.
				$except = !empty( $options[$key . UserOptionsLookup::LOCAL_EXCEPTION_SUFFIX] );

				// hidden prefs are added to options as well
				if ( isset( $saveOptions[$key] ) ) {
					$prefConfig = $preferences[ $key ];
					if ( !isset( $prefConfig['parent'] ) ) {
						$prefConfig['parent'] = $form;
					}
					$validated = $this->validateWithFormClass( $key, $prefConfig, $value );
					if ( !$validated ) {
						$wrongValidated[ $key ] = $validated;
						continue;
					}
				}

				$this->userOptionsManager->setOption( $user, $key, $value,
					$except ? UserOptionsManager::GLOBAL_OVERRIDE : UserOptionsManager::GLOBAL_IGNORE );
			}

		}

		if ( !empty( $wrongValidated ) ) {
			return $this->getResponseFactory()->createJson( [ 'success' => false, 'not-saved' => $wrongValidated ] );
		}
		$this->userOptionsManager->saveOptions( $user );
		// Necessary to save user realname etc
		$user->saveSettings();
		return $this->getResponseFactory()->createJson( [ 'success' => true ] );
	}

	/**
	 * @param string $key
	 * @param array $info
	 * @param mixed $value
	 * @return bool|string|Message
	 */
	private function validateWithFormClass( $key, $info, $value ) {
		if ( isset( $info['class'] ) ) {
			$class = $info['class'];
		} elseif ( isset( $info['type'] ) ) {
			$class = HTMLForm::$typeMappings[$info['type']];
			$info['class'] = $class;
		} else {
			$class = null;
		}
		if ( !$class ) {
			return false;
		}
		$info['fieldname'] = $key;
		$inputField = new $class( $info );
		$validated = $inputField->validate( $value, $info );
		return $validated;
	}

	/**
	 * @return array
	 */
	public function getSaveBlacklist() {
		return [
			'realname',
			'emailaddress',
		];
	}

	/** @inheritDoc */
	public function getBodyParamSettings(): array {
		return [
			'options' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false
			]
		];
	}

}
