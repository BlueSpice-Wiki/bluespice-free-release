<?php

namespace BlueSpice\Discovery;

interface ISkinLayoutAware {

	/**
	 * @param ISkinLayout $layout
	 * @return void
	 */
	public function setSkinLayout( ISkinLayout $layout ): void;
}
