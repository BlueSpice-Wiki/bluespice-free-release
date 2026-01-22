<?php

use MediaWiki\Maintenance\Maintenance;

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

/**
 * Convert old BSSocialProfile custom field configuration to
 * UserProfile format.
 */
class GetCustomFieldMigrationConfig extends Maintenance {

	/** @var array */
	private array $builtInFields = [
		'social-profile-department', 'social-profile-phone', 'social-profile-location', 'social-profile-function'
	];

	public function execute() {
		$raw = $GLOBALS['bsgBSSocialProfileCustomFields'] ?? [];
		if ( empty( $raw ) ) {
			$this->output( "No custom fields found.\n" );
			return;
		}
		foreach ( $raw as $key => $value ) {
			if ( in_array( $key, $this->builtInFields ) ) {
				continue;
			}
			$type = $this->typeMapping[$value['type']] ?? 'text';
			$formDefinition = [ 'type' => $type ];
			if ( $type === 'dropdown' ) {
				$formDefinition['options'] = $value['options'] ?? [];
			}
			$GLOBALS['wgUserProfileFields'][$key] = [
				'msgKey' => $value['i18n'],
				'formDefinition' => $formDefinition,
			];
		}

		$this->output( "Add this configuration to set up custom fields in UserProfile:\n" );
		$this->output( "\$GLOBALS['wgUserProfileFields'] = " .
			preg_replace(
				[ '/array \(/', '/\)(,?)/' ],
				[ '[', ']$1' ],
				var_export( $GLOBALS['wgUserProfileFields'], true )
			)
		);
	}
}

$maintClass = GetCustomFieldMigrationConfig::class;
require_once RUN_MAINTENANCE_IF_MAIN;
