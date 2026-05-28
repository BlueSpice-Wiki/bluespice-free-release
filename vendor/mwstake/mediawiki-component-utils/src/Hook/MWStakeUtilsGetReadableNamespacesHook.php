<?php

namespace MWStake\MediaWiki\Component\Utils\Hook;

interface MWStakeUtilsGetReadableNamespacesHook {
	/**
	 * @param array $allNamespaces
	 * @param array &$namespacePermissions
	 * @return bool
	 */
	public function onMWStakeUtilsGetReadableNamespaces( array $allNamespaces, array &$namespacePermissions ): bool;
}
