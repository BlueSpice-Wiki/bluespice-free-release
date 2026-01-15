<?php

$GLOBALS['wgAutoloadClasses']['BsWebInstaller'] = __DIR__ . '/includes/BsWebInstaller.php';
$GLOBALS['wgAutoloadClasses']['BsCliInstaller'] = __DIR__ . '/includes/BsCliInstaller.php';
require_once __DIR__ . '/includes/BsLocalSettingsGenerator.php';

$overrides['LocalSettingsGenerator'] = 'BsLocalSettingsGenerator';
$overrides['WebInstaller'] = 'BsWebInstaller';
$overrides['CliInstaller'] = 'BsCliInstaller';
