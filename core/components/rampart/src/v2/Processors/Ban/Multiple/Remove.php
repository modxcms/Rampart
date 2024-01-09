<?php

namespace Rampart\v2\Processors\Ban\Multiple;

class Remove extends \modProcessor
{
    public $classKey = 'rptBan';

    public function process()
    {
        if (!$bans = $this->getProperty('position')) {
            return $this->failure($this->modx->lexicon('rampart.ban_err_ns'));
        }

        $bans = explode(',', $bans);

        foreach ($bans as $ban) {
            $ban = $this->modx->getObject('rptBan', $ban);
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
