<?php

namespace Rampart\Elements\Snippet;

use Rampart\Rampart;

class Register extends Snippet
{
    public function run()
    {
        $hook = $this->sp['hook'];
        $fields = $this->sp['fields'];
        
        $username = $fields[$this->sp['usernameField']];
        $email = $fields['email'];

        $activationEmailTpl = $this->modx->getOption('activationEmailTpl', $this->sp, '');
        $activationEmailSubject = $this->modx->getOption('activationEmailSubject', $this->sp, '');
        $activationResourceId = $this->modx->getOption('activationResourceId', $this->sp, '');
        $rptSpammerErrorMessage = $this->modx->getOption(
            'rptSpammerErrorMessage',
            $this->sp,
            'Your account has been banned as a spammer. Sorry.'
        );

        $response = $this->rampart->check($username, $email);

        $hook->setValue('ip', $response[Rampart::IP]);
        $hook->setValue('hostname', $response[Rampart::HOSTNAME]);
        $hook->setValue('userAgent', $response[Rampart::USER_AGENT]);

        if ($response[Rampart::STATUS] == Rampart::STATUS_BANNED) {
            $hook->addError('username', $rptSpammerErrorMessage);
            return false;
        }
        if ($response[Rampart::STATUS] == Rampart::STATUS_MODERATED) {
            /* prevents confirmation email from being sent */
            $hook->setValue('register.moderate', true);

            $password = $this->rampart->encrypt($fields['password']);

            /* create a flagged user record */
            /** @var $flu */
            if ($this->sp['modx3']) {
                $flu = $this->modx->newObject(\Rampart\Model\FlaggedUser::class);
            } else {
                $flu = $this->modx->newObject('rptFlaggedUser');
            }
            $flu->set('username', $response[Rampart::USERNAME]);
            $flu->set('password', $password);
            $flu->set('ip', $response[Rampart::IP]);
            $flu->set('hostname', $response[Rampart::HOSTNAME]);
            $flu->set('useragent', $response[Rampart::USER_AGENT]);
            $flu->set('flaggedfor', $response[Rampart::REASON]);
            $flu->set('activation_email_tpl', $activationEmailTpl);
            $flu->set('activation_email_subject', $activationEmailSubject);
            $flu->set('activation_resource_id', $activationResourceId);
            $flu->set('flaggedon', time());
            $flu->save();
            return true;
        }

        return true;
    }
}
