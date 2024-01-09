<?php

namespace Rampart\Elements\Snippet;

use Rampart\Rampart;

class Quip extends Snippet
{
    public function run()
    {
        $hook = $this->sp['hook'];
        $fields = $this->sp['fields'];
        $email = $fields['email'];

        $rptSpammerErrorMessage = $this->modx->getOption(
            'rptSpammerErrorMessage',
            $this->sp,
            'Your account has been banned as a spammer. Sorry.'
        );

        $response = $this->rampart->check('', $email);

        $hook->setValue('ip', $response[Rampart::IP]);
        $hook->setValue('hostname', $response[Rampart::HOSTNAME]);
        $hook->setValue('userAgent', $response[Rampart::USER_AGENT]);

        if ($response[Rampart::STATUS] == Rampart::STATUS_BANNED) {
            $hook->addError('email', $rptSpammerErrorMessage);
            return false;
        }

        return true;
    }
}
