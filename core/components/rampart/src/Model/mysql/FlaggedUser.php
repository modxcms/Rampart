<?php
namespace Rampart\Model\mysql;

use xPDO\xPDO;

class FlaggedUser extends \Rampart\Model\FlaggedUser
{

    public static $metaMap = array (
        'package' => 'Rampart\\Model\\',
        'version' => '3.0',
        'table' => 'rampart_flagged_users',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'username' => '',
            'password' => '',
            'flaggedon' => NULL,
            'flaggedfor' => '',
            'ip' => NULL,
            'hostname' => NULL,
            'useragent' => NULL,
            'status' => '',
            'actedon' => NULL,
            'actedby' => 0,
            'activation_email_tpl' => '',
            'activation_email_subject' => NULL,
            'activation_resource_id' => 0,
        ),
        'fieldMeta' => 
        array (
            'username' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
                'index' => 'index',
            ),
            'password' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'flaggedon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'flaggedfor' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'ip' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '60',
                'phptype' => 'string',
                'null' => true,
            ),
            'hostname' => 
            array (
                'dbtype' => 'tinytext',
                'phptype' => 'string',
                'null' => true,
            ),
            'useragent' => 
            array (
                'dbtype' => 'tinytext',
                'phptype' => 'string',
                'null' => true,
            ),
            'status' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '10',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
                'index' => 'index',
            ),
            'actedon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'actedby' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
            'activation_email_tpl' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'activation_email_subject' => 
            array (
                'dbtype' => 'tinytext',
                'phptype' => 'string',
                'null' => true,
            ),
            'activation_resource_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'index',
            ),
        ),
        'indexes' => 
        array (
            'username' => 
            array (
                'alias' => 'username',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'username' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'status' => 
            array (
                'alias' => 'status',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'status' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'actedby' => 
            array (
                'alias' => 'actedby',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'actedby' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'activation_resource_id' => 
            array (
                'alias' => 'activation_resource_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'activation_resource_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'User' => 
            array (
                'class' => 'MODX\\Revolution\\modUser',
                'local' => 'username',
                'foreign' => 'username',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'ActivationResource' => 
            array (
                'class' => 'MODX\\Revolution\\modResource',
                'local' => 'activation_resource_id',
                'foreign' => 'username',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
