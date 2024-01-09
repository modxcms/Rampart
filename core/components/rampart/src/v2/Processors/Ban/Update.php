<?php

namespace Rampart\v2\Processors\Ban;

class Update extends \modObjectUpdateProcessor
{
    public $classKey = 'rptBan';
    public $objectType = 'rampart.ban';
    public $languageTopics = array('rampart:default');

    public function beforeSave()
    {
        $this->object->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('editedby', $this->modx->user->get('id'));
        return parent::beforeSave();
    }
}
