<?php

$corePath = $modx->getOption(
    'rampart.core_path',
    null,
    $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/rampart/'
);

require_once($corePath . 'vendor/autoload.php');

if (!isset($scriptProperties)) {
    $scriptProperties = [];
}

if (empty($modx->version)) {
    $modx->getVersionData();
}
$scriptProperties['modx3'] = ($modx->version['version'] >= 3);
if ($modx->version['version'] < 3) {
    $rampart = $modx->getService(
        'rampart',
        'Rampart',
        $corePath . 'model/rampart/',
        [
            'core_path' => $corePath
        ]
    );
} else {
    $rampart = new \Rampart\Rampart($modx);
}

$class = "\\Rampart\\Elements\\Event\\{$modx->event->name}";

if (class_exists($class)) {
    $plugin = new $class($rampart, $scriptProperties);
    return $plugin->run();
} else {
    return $modx->lexicon('rampart.plugin.error.nf');
}
