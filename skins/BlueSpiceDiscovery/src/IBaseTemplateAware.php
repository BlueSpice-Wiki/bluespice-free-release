<?php

namespace BlueSpice\Discovery;

use BaseTemplate;

interface IBaseTemplateAware {

	/**
	 * @param BaseTemplate $baseTemplate
	 * @return void
	 */
	public function setBaseTemplate( BaseTemplate $baseTemplate ): void;
}
