<?php

namespace BlueSpice\PageAssignments\Hook\NamespaceManagerGetMetaFields;

use BlueSpice\NamespaceManager\Hook\NamespaceManagerGetMetaFields;

class RegisterMetaFields extends NamespaceManagerGetMetaFields {

	/**
	 * @inheritDoc
	 */
	protected function doProcess() {
		$this->metaFields[] = [
			'name' => 'pageassignments-secure',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-pageassignments-secure-nsm-label' )->text(),
			'filter' => [
				'type' => 'boolean'
			]
		];

		return true;
	}
}
