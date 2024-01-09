<?php

namespace Rampart\v2\Processors\Flagged;

class GetList extends \modObjectGetListProcessor
{

    public $classKey = 'modUser';
    public $objectType = 'rampart.flag';
    public $defaultSortField = 'username';
    public $defaultSortDirection = 'DESC';
    public $languageTopics = array('rampart:default');
    public function initialize()
    {
        $initialized = parent::initialize();
        $this->setDefaultProperties(array(
            'search' => false,
            'status' => '',
        ));
        return $initialized;
    }

    public function prepareQueryBeforeCount(\xPDOQuery $c)
    {
        $c->innerJoin('rptFlaggedUser', 'Flag', 'Flag.username = modUser.username');
        $c->innerJoin('modUserProfile', 'Profile');
        if (!empty($search)) {
            $c->where(array(
                'modUser.username:LIKE' => '%'.$search.'%',
                'OR:Profile.email:LIKE' => '%'.$search.'%',
                'OR:Profile.fullname:LIKE' => '%'.$search.'%',
            ), null, null, 2);
        }
        $c->where(array(
            'Flag.status' => $this->getProperty('status'),
        ));
        return $c;
    }

    public function prepareQueryAfterCount(\xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns('modUser', 'modUser'));
        $c->select($this->modx->getSelectColumns('modUserProfile', 'Profile', '', array(
            'email','fullname',
        )));
        $c->select($this->modx->getSelectColumns('rptFlaggedUser', 'Flag', '', array(
            'ip','hostname','useragent','flaggedfor','flaggedon','approved',
        )));
        return $c;
    }

    public function prepareRow(\xPDOObject $object)
    {
        $objectArray = $object->toArray();
        $objectArray['active'] = (boolean)$object->get('active');
        $objectArray['flaggedon'] = strftime('%b %d, %Y %I:%M %p', strtotime($object->get('flaggedon')));
        $objectArray['flaggedfor'] = $this->modx->lexicon('rampart.flag_'.$object->get('flaggedfor'));
        return $objectArray;
    }
}
