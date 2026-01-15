<?php

use BlueSpice\Api\Response\Standard;
use BlueSpice\Avatars\Generator;

class BSApiAvatarsTasks extends BSApiTasksBase {

	/**
	 *
	 * @var array
	 */
	protected $aTasks = [
		'uploadFile' => [
			'examples' => [],
			'params' => []
		],
		'generateAvatar' => [
			'examples' => [],
			'params' => []
		]
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'uploadFile' => [ 'upload' ],
			'generateAvatar' => [ 'read' ],
		];
	}

	// phpcs:disable
	/**
	 *
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 * @throws MWException
	 */
	public function task_uploadFile( $oTaskData, $aParams ) {
		// phpcs:enable
		$oResponse = $this->makeStandardReturn();
		$oUser = $this->getUser();
		\BlueSpice\Avatars\Extension::unsetUserImage( $oUser );
		$sAvatarFileName = Generator::FILE_PREFIX . $oUser->getId() . ".png";
		$oStatus = BsFileSystemHelper::uploadAndConvertImage(
			$this->getRequest()->getVal( 'name' ),
			'Avatars',
			$sAvatarFileName
		);
		if ( !$oStatus->isGood() ) {
			$oResponse->message = $oStatus->getMessage()->text();
			return $oResponse;
		}

		# found no way to regenerate thumbs. just delete thumb folder if it exists
		$oStatus = BsFileSystemHelper::deleteFolder(
			"Avatars/thumb/$sAvatarFileName",
			true
		);
		if ( !$oStatus->isGood() ) {
			throw new MWException( 'FATAL: Avatar thumbs could no be deleted!' );
		}

		$oResponse->message = $this->msg( 'bs-avatars-upload-complete' )->plain();
		$oResponse->success = true;
		return $oResponse;
	}

	// phpcs:disable
	/**
	 *
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 * @throws MWException
	 */
	public function task_generateAvatar( $oTaskData, $aParams ) {
		// phpcs:enable
		$oResponse = $this->makeStandardReturn();

		$oUser = $this->getUser();
		\BlueSpice\Avatars\Extension::unsetUserImage( $oUser );
		$generator = $this->services->getService( 'BSAvatarsAvatarGenerator' );
		$generator->generate( $oUser, [ Generator::PARAM_OVERWRITE => true ] );

		$oResponse->success = true;
		$oResponse->message = $this->msg( 'bs-avatars-generate-complete' )->plain();
		return $oResponse;
	}

}
