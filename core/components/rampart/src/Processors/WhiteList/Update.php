<?php

namespace Rampart\Processors\WhiteList;

class Update extends \MODX\Revolution\Processors\Model\UpdateProcessor
{
    public $classKey = \Rampart\Model\WhiteList::class;
    public $objectType = 'rampart.whitelist';
    public $languageTopics = array('rampart:default');

    public function beforeSave()
    {
        $this->object->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('editedby', $this->modx->user->get('id'));
        return parent::beforeSave();
    }
}
