<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Droplet;

abstract class BehaviourSwitchDroplet extends MagicWordDroplet {

	/**
	 * @return string
	 */
	public function getContent(): string {
		return '__' . strtoupper( $this->getMagicWord() ) . '__';
	}

}
