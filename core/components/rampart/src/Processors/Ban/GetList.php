<?php

namespace Rampart\Processors\Ban;

class GetList extends \MODX\Revolution\Processors\Model\GetListProcessor
{
    public $classKey = \Rampart\Model\Ban::class;
    public $objectType = 'rampart.ban';
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
