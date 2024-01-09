<?php

namespace Rampart\v2\Processors\WhiteList;

class Update extends \modObjectUpdateProcessor
{
    public $classKey = 'rptWhiteList';
    public $objectType = 'rampart.whitelist';
    public $languageTopics = array('rampart:default');

    public function beforeSave()
    {
        $this->object->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('editedby', $this->modx->user->get('id'));
        return parent::beforeSave();
    }
}
