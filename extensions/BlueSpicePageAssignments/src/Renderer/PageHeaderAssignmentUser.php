<?php
namespace BlueSpice\PageAssignments\Renderer;

use MediaWiki\MediaWikiServices;

class PageHeaderAssignmentUser extends PageHeaderAssignmentBase {
	public const PARAM_ASSIGNMENT = 'assignment';

	/**
	 *
	 * @var IAssignment
	 */
	protected $assignment = null;

	/**
	 * Returns an array of arguments
	 * @return array
	 */
	public function getArgs() {
		$args = parent::getArgs();

		$services = MediaWikiServices::getInstance();
		$user = $services->getUserFactory()->newFromName( $this->assignment->getKey() );

		$util = $services->getService( 'BSUtilityFactory' );

		$userLink = $this->linkRenderer->makeLink(
			$user->getUserPage(),
			$util->getUserHelper( $user )->getDisplayName()
		);

		$args['text'] = $userLink;
		return $args;
	}
}
