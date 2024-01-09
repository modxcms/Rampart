<?php

namespace Rampart\Processors\WhiteList\Multiple;

class Deactivate extends \MODX\Revolution\Processors\Processor
{
    public $classKey = \Rampart\Model\WhiteList::class;

    public function process()
    {
        if (!$whitelists = $this->getProperty('position')) {
            return $this->failure($this->modx->lexicon('rampart.whitelist_err_ns'));
        }

        $whitelists = explode(',', $whitelists);

        foreach ($whitelists as $whitelist) {
            $whitelist = $this->modx->getObject(\Rampart\Model\WhiteList::class, $whitelist);
            if (empty($whitelist)) {
                continue;
            }
            $whitelist->set('active', false);

            if ($whitelist->save() === false) {
                return $this->failure($this->modx->lexicon('rampart.whitelist_err_save'));
            }
        }

        return $this->success();
    }
}
