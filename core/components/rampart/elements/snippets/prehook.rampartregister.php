<?php

/**
 * Rampart
 *
 * Copyright 2011 by Shaun McCormick <shaun@modx.com>
 *
 * Rampart is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Rampart is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Rampart; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package rampart
 */
/**
 * preHook for Register snippet that utilizes Rampart
 *
 * @var modX $modx
 * @var Rampart $rampart
 * @var array $scriptProperties
 * @var LoginHooks $hook
 * @var array $fields
 * @var string $usernameField
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

$snippet = new \Rampart\Elements\Snippet\Register($rampart, $scriptProperties);
return $snippet->run();
