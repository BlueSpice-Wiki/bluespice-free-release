<?php

namespace BlueSpice\WhoIsOnline\Hook\BSFoundationRendererMakeTagAttribs;

use BlueSpice\Hook\BSFoundationRendererMakeTagAttribs;
use BlueSpice\Renderer\UserImage;
use BlueSpice\WhoIsOnline\Tracer;

class AddUserProfileOnlineMarkerMetaData extends BSFoundationRendererMakeTagAttribs {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->renderer instanceof UserImage ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$user = $this->renderer->getUser();
		$this->attribs['data-bs-whoisonline-marker'] = $user->getName();
		$status = $this->getTracer()->getUserOnlineStatus( $user );
		$this->attribs['class'] .= " bs-whoisonline-marker-$status";
		return true;
	}

	/**
	 *
	 * @return Tracer
	 */
	private function getTracer() {
		return $this->getServices()->getService( 'BSWhoIsOnlineTracer' );
	}
}
