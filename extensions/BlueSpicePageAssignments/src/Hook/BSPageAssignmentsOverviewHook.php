<?php

namespace BlueSpice\PageAssignments\Hook;

interface BSPageAssignmentsOverviewHook {

	/**
	 * @param array &$deps
	 */
	public function onBSPageAssignmentsOverview( array &$deps ): void;
}
