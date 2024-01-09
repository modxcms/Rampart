<?php

namespace Rampart\v2\Processors\Flagged;

class Approve extends \modProcessor
{

    public function process()
    {
        if (!$users = $this->getProperty('users')) {
            return $this->failure($this->modx->lexicon('rampart.flagged_err_ns'));
        }
        $users = explode(',', $users);
        
        foreach ($users as $user) {
            $user = $this->modx->getObject('modUser', $user);
            if (empty($user)) {
                continue;
            }
            $flaggedUser = $this->modx->getObject('rptFlaggedUser', array('username' => $user->get('username')));
            if (empty($flaggedUser)) {
                continue;
            }


            if (!$flaggedUser->sendActivationEmail($this->modx->rampart)) {
                $this->modx->log(
                    \modX::LOG_LEVEL_ERROR,
                    '[Rampart] Could not send activation email for: '.$user->get('username')
                );
                continue;
            }

            $user->set('active', true);
            $flaggedUser->set('status', 'approved');
            $flaggedUser->set('actedon', time());
            $flaggedUser->set('actedby', $this->modx->user->get('id'));

            $user->save();
            $flaggedUser->save();
        }
        return $this->success();
    }
}
