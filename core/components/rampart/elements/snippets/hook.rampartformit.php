<?php

/**
 * Hook for FormIt forms
 *
 * @var modX $modx
 * @var Rampart $rampart
 * @var array $scriptProperties
 * @var fiHooks $hook
 * @var array $fields
 * @package rampart
 */

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

$snippet = new \Rampart\Elements\Snippet\FormIt($rampart, $scriptProperties);
return $snippet->run();
