<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_INPUTPROCESSOR_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_INPUTPROCESSOR_VERSION', '1.1.2' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'inputprocessor', static function () {
	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';
	$GLOBALS['wgMessagesDirs']['mwstake-component-inputprocessor'] = __DIR__ . '/i18n';

	$GLOBALS['mwsgInputProcessorRegistry'] = [
		'integer' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\IntValue'
		],
		'integer-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\IntListValue'
		],
		'boolean' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\BoolValue'
		],
		'keyword' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\KeywordValue'
		],
		'keyword-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\KeywordListValue'
		],
		'title' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\TitleValue',
			'services' => [ 'TitleFactory', 'PermissionManager' ]
		],
		'title-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\TitleListValue',
			'services' => [ 'TitleFactory', 'PermissionManager' ]
		],
		'namespace' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\NamespaceValue',
			'services' => [ 'NamespaceInfo', 'ContentLanguage' ]
		],
		'namespace-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\NamespaceListValue',
			'services' => [ 'NamespaceInfo', 'ContentLanguage' ]
		],
		'category' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\CategoryValue',
			'services' => [ 'TitleFactory', 'PermissionManager', 'DBLoadBalancer' ]
		],
		'category-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\CategoryListValue',
			'services' => [ 'TitleFactory', 'PermissionManager', 'DBLoadBalancer' ]
		],
		'username' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\UsernameValue',
			'services' => [ 'UserFactory' ]
		],
		'username-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\UsernameListValue',
			'services' => [ 'UserFactory' ]
		],
		'usergroup' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\UserGroupValue',
			'services' => [ 'UserGroupManager' ]
		],
		'usergroup-list' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\UserGroupListValue',
			'services' => [ 'UserGroupManager' ]
		],
		'string' => [
			'class' => '\\MWStake\\MediaWiki\\Component\\InputProcessor\\Processor\\StringValue'
		],
		'string_list' => [
			'class' => \MWStake\MediaWiki\Component\InputProcessor\Processor\StringListValue::class,
		],
		'percent' => [
			'class' => \MWStake\MediaWiki\Component\InputProcessor\Processor\PercentValue::class,
		]
	];
} );
