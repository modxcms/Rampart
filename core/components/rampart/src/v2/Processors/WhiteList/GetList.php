<?php

namespace Rampart\v2\Processors\WhiteList;

class GetList extends \modObjectGetListProcessor
{
    public $classKey = 'rptWhiteList';
    public $objectType = 'rampart.whitelist';
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'DESC';
    public $languageTopics = array('rampart:default');

    public function initialize()
    {
        $initialized = parent::initialize();
        $this->setDefaultProperties(array(
            'search' => false,
        ));
        return $initialized;
    }

    public function prepareQueryBeforeCount(\xPDOQuery $c)
    {
        $search = $this->getProperty('search', null);
        if (!empty($search)) {
            $c->where(array(
                'reason:LIKE' => '%'.$search.'%',
                'OR:hostname:LIKE' => '%'.$search.'%',
                'OR:email:LIKE' => '%'.$search.'%',
                'OR:username:LIKE' => '%'.$search.'%',
                'OR:ip:LIKE' => '%'.$search.'%',
            ));
        }
        return $c;
    }

    public function prepareRow(\xPDOObject $object)
    {
        $objectArray = $object->toArray();
        $objectArray['ip'] = $object->get('ip');
        $objectArray['active'] = (boolean)$object->get('active');
        return $objectArray;
    }
}
