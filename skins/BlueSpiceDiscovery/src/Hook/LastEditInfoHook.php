<?php

namespace BlueSpice\Discovery\Hook;

use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionRecord;

interface LastEditInfoHook {

	/**
	 * @param RevisionRecord $revision
	 * @param string $revisionDiffLink
	 * @param string $lastEditorLink
	 * @param Message &$lastEditInfo
	 */
	public function onLastEditInfo(
		RevisionRecord $revision, string $revisionDiffLink, string $lastEditorLink,	Message &$lastEditInfo
	): void;
}
