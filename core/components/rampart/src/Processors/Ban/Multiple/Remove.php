<?php

namespace Rampart\Processors\Ban\Multiple;

class Remove extends \MODX\Revolution\Processors\Processor
{
    public $classKey = \Rampart\Model\Ban::class;

    public function process()
    {
        if (!$bans = $this->getProperty('position')) {
            return $this->failure($this->modx->lexicon('rampart.ban_err_ns'));
        }

        $bans = explode(',', $bans);

        foreach ($bans as $ban) {
            $ban = $this->modx->getObject(\Rampart\Model\Ban::class, $ban);
            if (empty($ban)) {
                continue;
            }

            if ($ban->remove() === false) {
                return $this->failure($this->modx->lexicon('rampart.ban_err_remove'));
            }
        }

        return $this->success();
    }
}
