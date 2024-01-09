<?php

namespace Rampart\Processors\Ban;

class Create extends \MODX\Revolution\Processors\Model\CreateProcessor
{
    public $classKey = \Rampart\Model\Ban::class;
    public $objectType = 'rampart.ban';
    public $languageTopics = array('rampart:default');

    public function beforeSave()
    {
        $this->object->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('createdby', $this->modx->user->get('id'));
        return parent::beforeSave();
    }
}
