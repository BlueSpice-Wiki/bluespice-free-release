<?php
namespace BlueSpice\PageAssignments\Renderer;

use BlueSpice\Renderer\Params;
use MediaWiki\MediaWikiServices;

class User extends Assignment {
	public const PARAM_ASSIGNMENT = 'assignment';

	/**
	 *
	 * @var IAssignment
	 */
	protected $assignment = null;

	/**
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	protected function render_image( $val ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$services = MediaWikiServices::getInstance();
		$renderer = $services->getService( 'BSRendererFactory' )->get(
			'userimage',
			new Params( [
				'user' => $services->getUserFactory()->newFromName( $this->assignment->getKey() )
			] )
		);
		return $renderer->render();
	}

}
