<?php

namespace Rampart\Processors\Ban;

class Activate extends \MODX\Revolution\Processors\ModelProcessor
{
    public $classKey = \Rampart\Model\Ban::class;
    public $objectType = 'rampart.ban';
    public $languageTopics = array('rampart:default');

    public function initialize()
    {
        $id = $this->getProperty('id', false);
        if (empty($id)) {
            return $this->modx->lexicon('rampart.ban_err_ns');
        }
        $this->object = $this->modx->getObject($this->classKey, $id);
        if (empty($this->object)) {
            return $this->modx->lexicon('rampart.ban_err_nf');
        }
        return true;
    }
    public function process()
    {
        $this->object->set('active', true);
        $this->object->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
        $this->object->set('editedby', $this->modx->user->get('id'));

        if ($this->object->save() === false) {
            return $this->failure($this->modx->lexicon('rampart.ban_err_save'));
        }

        return $this->success('', $this->object);
    }
}
