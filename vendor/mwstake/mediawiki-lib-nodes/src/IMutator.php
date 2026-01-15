<?php

namespace MWStake\MediaWiki\Lib\Nodes;

use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\User;

interface IMutator extends IParser {
	/**
	 * @return string|null if no mutations happened
	 */
	public function getMutatedData(): ?string;

	/**
	 * @param User|null $user Actor
	 * @param string $comment
	 * @param int $flags
	 * @return RevisionRecord|null
	 */
	public function saveRevision( $user = null, $comment = '', $flags = 0 ): ?RevisionRecord;

	/**
	 * @return RevisionRecord
	 */
	public function getRevision(): RevisionRecord;

	/**
	 * @param INode $node
	 * @param string $mode
	 * @param bool $newline
	 * @return void
	 */
	public function addNode( INode $node, $mode = 'append', $newline = true ): void;

	/**
	 * @param IMutableNode $node
	 * @return bool
	 */
	public function replaceNode( IMutableNode $node ): bool;

	/**
	 * @param INode $node
	 * @return bool
	 */
	public function removeNode( INode $node ): bool;
}
