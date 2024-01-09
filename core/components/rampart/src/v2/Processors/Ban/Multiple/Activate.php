<?php

namespace Rampart\v2\Processors\Ban\Multiple;

class Activate extends \modProcessor
{
    public $languageTopics = array('rampart:default');

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
            $ban->set('active', true);

            if ($ban->save() === false) {
                return $this->failure($this->modx->lexicon('rampart.ban_err_save'));
            }
        }

        return $this->success();
    }
}
