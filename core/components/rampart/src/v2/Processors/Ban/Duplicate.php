<?php

namespace Rampart\v2\Processors\Ban;

class Duplicate extends \modObjectProcessor
{
    public $classKey = 'rptBan';
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
        /** @var rptBan $newBan */
        $newBan = $this->modx->newObject($this->classKey);
        $newBan->fromArray($this->object->toArray());
        $newBan->set('editedon', null);
        $newBan->set('editedby', 0);
        $newBan->set('createdon', time());
        $newBan->set('active', 0);

        if ($newBan->save() === false) {
            return $this->failure($this->modx->lexicon('rampart.ban_err_duplicate'));
        }

        return $this->success('', $newBan);
    }
}
