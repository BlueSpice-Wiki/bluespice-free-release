<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use Exception;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Extension\UserProfile\Widget\UserProfile;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Message\Message;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\InputProcessor\Processor\BooleanValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\StringListValue;
use MWStake\MediaWiki\Component\InputProcessor\Processor\UserValue;
use MWStake\MediaWiki\Component\InputProcessor\Runner;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;

class AddTag implements ParserFirstCallInitHook {

	/** @var Runner */
	private $inputProcessorRunner;

	/** @var UserFactory */
	private $userFactory;

	/** @var ProfileManager */
	private $profileManager;

	/**
	 * @param Runner $inputProcessorRunner
	 * @param UserFactory $userFactory
	 * @param ProfileManager $profileManager
	 */
	public function __construct(
		Runner $inputProcessorRunner, UserFactory $userFactory, ProfileManager $profileManager
	) {
		$this->inputProcessorRunner = $inputProcessorRunner;
		$this->userFactory = $userFactory;
		$this->profileManager = $profileManager;
	}

	/**
	 * @inheritDoc
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'user-profile', [ $this, 'renderUserProfile' ] );
		$parser->setHook( 'bs:socialentityprofile', [ $this, 'renderLegacy' ] );
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 * @throws \OOUI\Exception
	 */
	public function renderUserProfile( $input, array $args, $parser ) {
		OutputPage::setupOOUI();
		$parser->getOutput()->setEnableOOUI( true );
		try {
			$validated = $this->getValidatedArgs( $args );
		} catch ( Exception $e ) {
			return new MessageWidget( [
				'type' => 'error',
				'label' => new HtmlSnippet(
					Message::newFromKey( 'userprofile-tag-error', $e->getMessage() )->parse()
				)
			] );
		}
		$user = $validated['user'];
		$parser->getOutput()->addModuleStyles( [ 'ext.userProfile.styles' ] );
		// Parser does have user object available, but its not set on API calls
		$requester = RequestContext::getMain()->getUser();
		$data = $this->profileManager->getProfileData( $user, $requester );
		$availableFields = $this->profileManager->getFieldRegistry()->getSerializedFields(
			$user, $requester, $parser->getContentLanguage()
		);
		// if $validated['fields'], only consider ones listed there
		if ( !empty( $validated['fields'] ) ) {
			// Mandatory fields
			$validated['fields'][] = 'username';
			$validated['fields'][] = 'realName';
			$validated['fields'][] = 'userDisplay';
			$validated['fields'][] = 'imageUrl';
			$availableFields = array_intersect_key( $availableFields, array_flip( $validated['fields'] ) );
		}

		$data['fields'] = $availableFields;
		$widget = new UserProfile( array_merge( [
			'framed' => $validated['framed'],
		], $data ) );
		if ( $validated['orientation'] === 'vertical' ) {
			$widget->addClasses( [ 'user-profile-vertical' ] );
		}
		return $widget->toString();
	}

	/**
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 * @throws \OOUI\Exception
	 */
	public function renderLegacy( string $input, array $args, $parser ) {
		return $this->renderUserProfile( $input, [
			'user' => $args['username'] ?? ''
		], $parser );
	}

	/**
	 * @param array $args
	 * @return array
	 * @throws Exception
	 */
	private function getValidatedArgs( array $args ): array {
		$userValidator = new UserValue( $this->userFactory );
		$userValidator->setRequired( true );

		$framedValidator = new BooleanValue();
		$framedValidator->setRequired( false );
		$framedValidator->setDefaultValue( true );

		$orientationValidator = new KeywordValue();
		$orientationValidator->setRequired( false );
		$orientationValidator->setDefaultValue( 'horizontal' );
		$orientationValidator->setKeywords( [ 'horizontal', 'vertical' ] );

		$fieldsValidator = new StringListValue();
		$fieldsValidator->setRequired( false );
		$fieldsValidator->setListSeparator( ',' );

		$processors = [
			'user' => $userValidator,
			'framed' => $framedValidator,
			'orientation' => $orientationValidator,
			'fields' => $fieldsValidator
		];

		$status = $this->inputProcessorRunner->process( $processors, $args );
		if ( $status->isGood() ) {
			return $status->getValue();
		} else {
			$errors = $status->getErrors();
			$messages = [];
			foreach ( $errors as $error ) {
				$messages[] = Message::newFromKey( $error['message'] )->params( ...$error['params'] )->parse();
			}
			throw new Exception( implode( "<br>", $messages ) );
		}
	}
}
