<?php

namespace Rampart\Processors\WhiteList;

class Duplicate extends \MODX\Revolution\Processors\ModelProcessor
{
    public $classKey = \Rampart\Model\WhiteList::class;
    public $objectType = 'rampart.whitelist';
    public $languageTopics = array('rampart:default');

    public function initialize()
    {
        $id = $this->getProperty('id', false);
        if (empty($id)) {
            return $this->modx->lexicon('rampart.whitelist_err_ns');
        }
        $this->object = $this->modx->getObject($this->classKey, $id);
        if (empty($this->object)) {
            return $this->modx->lexicon('rampart.whitelist_err_nf');
        }
        return true;
    }

    public function process()
    {
        /** @var rptWhiteList $newWhiteList */
        $newWhiteList = $this->modx->newObject($this->classKey);
        $newWhiteList->fromArray($this->object->toArray());
        $newWhiteList->set('editedon', null);
        $newWhiteList->set('editedby', 0);
        $newWhiteList->set('createdon', time());
        $newWhiteList->set('active', 0);

        if ($newWhiteList->save() === false) {
            return $this->failure($this->modx->lexicon('rampart.whitelist_err_duplicate'));
        }

        return $this->success('', $newWhiteList);
    }
}
