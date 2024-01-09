<?php

namespace Rampart\Processors\Ban\Multiple;

class Activate extends \MODX\Revolution\Processors\Processor
{
    public $languageTopics = array('rampart:default');

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
            $ban->set('active', true);

            if ($ban->save() === false) {
                return $this->failure($this->modx->lexicon('rampart.ban_err_save'));
            }
        }

        return $this->success();
    }
}
