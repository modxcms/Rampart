<?php

namespace Rampart\Processors\Flagged;

class Reject extends \MODX\Revolution\Processors\Processor
{

    public function process()
    {
        if (!$users = $this->getProperty('users')) {
            return $this->failure($this->modx->lexicon('rampart.flagged_err_ns'));
        }
        $users = explode(',', $users);

        foreach ($users as $user) {
            $user = $this->modx->getObject(\MODX\Revolution\modUser::class, $user);
            if (empty($user)) {
                continue;
            }
            $flaggedUser = $this->modx->getObject(\Rampart\Model\FlaggedUser::class, array('username' => $user->get('username')));
            if (empty($flaggedUser)) {
                continue;
            }

            $user->set('active', true);
            $flaggedUser->set('status', 'rejected');
            $flaggedUser->set('actedon', time());
            $flaggedUser->set('actedby', $this->modx->user->get('id'));

            $user->save();
            $flaggedUser->save();
        }
        return $this->success();
    }
}
