<?php
namespace Rampart\Model\mysql;

use xPDO\xPDO;

class BanMatchField extends \Rampart\Model\BanMatchField
{

    public static $metaMap = array (
        'package' => 'Rampart\\Model\\',
        'version' => '3.0',
        'table' => 'rampart_ban_matches_bans',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'ban' => 0,
            'ban_match' => 0,
            'field' => '',
        ),
        'fieldMeta' => 
        array (
            'ban' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'pk',
            ),
            'ban_match' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'pk',
            ),
            'field' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '60',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
                'index' => 'pk',
            ),
        ),
        'indexes' => 
        array (
            'PRIMARY' => 
            array (
                'alias' => 'PRIMARY',
                'primary' => true,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'ban' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'ban_match' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'field' => 
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
            'Ban' => 
            array (
                'class' => 'Rampart\\Model\\Ban',
                'local' => 'ban',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'BanMatch' => 
            array (
                'class' => 'Rampart\\Model\\BanMatch',
                'local' => 'ban_match',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
