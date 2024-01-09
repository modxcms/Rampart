<?php
/**
 * Resolve creating db tables
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package rampart
 * @subpackage build
 *
 * @var mixed $object
 * @var modX $modx
 * @var array $options
 */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('rampart.core_path', null, $modx->getOption('core_path') . 'components/rampart/') . 'model/';
            
            $modx->addPackage('rampart', $modelPath, null);


            $manager = $modx->getManager();

            $manager->createObjectContainer('rptBan');
            $manager->createObjectContainer('rptFlaggedUser');
            $manager->createObjectContainer('rptBanMatch');
            $manager->createObjectContainer('rptBanMatchField');
            $manager->createObjectContainer('rptBanMatchBan');
            $manager->createObjectContainer('rptWhiteList');

            break;
    }
}

return true;