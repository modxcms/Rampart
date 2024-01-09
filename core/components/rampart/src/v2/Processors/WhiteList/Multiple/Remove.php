<?php

namespace Rampart\v2\Processors\WhiteList\Multiple;

class Remove extends \modProcessor
{
    public $classKey = 'rptWhiteList';

    public function process()
    {
        if (!$whitelists = $this->getProperty('position')) {
            return $this->failure($this->modx->lexicon('rampart.whitelist_err_ns'));
        }

        $whitelists = explode(',', $whitelists);

        foreach ($whitelists as $whitelist) {
            $whitelist = $this->modx->getObject('rptWhiteList', $whitelist);
            if (empty($whitelist)) {
                continue;
            }

            if ($whitelist->remove() === false) {
                return $this->failure($this->modx->lexicon('rampart.whitelist_err_remove'));
            }
        }

        return $this->success();
    }
}
