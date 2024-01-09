<?php

namespace Rampart\Elements\Snippet;

use Rampart\Rampart;

class FormIt extends Snippet
{
    public function run()
    {
        /* setup default properties */
        $rptErrorField = $this->modx->getOption('rptErrorField', $this->sp, 'email');
        $rptUsernameField = $this->modx->getOption('rptUsernameField', $this->sp, 'username');
        $rptEmailField = $this->modx->getOption('rptEmailField', $this->sp, 'email');
        $rptSpammerErrorMessage = $this->modx->getOption(
            'rptSpammerErrorMessage',
            $this->sp,
            'Your account has been banned as a spammer. Sorry.'
        );

        /* get username/email if they exist */
        $username = '';
        if (!empty($fields[$rptUsernameField])) {
            $username = $fields[$rptUsernameField];
        }
        $email = '';
        if (!empty($fields[$rptEmailField])) {
            $email = $fields[$rptEmailField];
        }

        /* run ban checking */
        $response = $this->rampart->check($username, $email);

        if ($response[Rampart::STATUS] == Rampart::STATUS_BANNED) {
            $this->sp['hook']->addError($rptErrorField, $rptSpammerErrorMessage);
            return false;
        }
        return true;
    }
}
