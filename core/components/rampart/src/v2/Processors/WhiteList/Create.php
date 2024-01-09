<?php

namespace Rampart\v2\Processors\WhiteList;

class Create extends \modObjectCreateProcessor
{
    public $classKey = 'rptWhiteList';
    public $objectType = 'rampart.whitelist';
    public $languageTopics = array('rampart:default');

    public function beforeSave()
    {
        $this->object->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('createdby', $this->modx->user->get('id'));
        return parent::beforeSave();
    }
}
