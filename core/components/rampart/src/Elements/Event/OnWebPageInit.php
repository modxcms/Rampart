<?php

namespace Rampart\Elements\Event;

use Rampart\HoneyPot;
use Rampart\Rampart;

class OnWebPageInit extends Event
{
    public function run()
    {
        if ($this->modx->getOption('rampart.honeypot.enabled', null, false) &&
            $this->modx->getOption('rampart.honeypot.fullwall_enabled', null, false)) {
            /* handle ProjectHoneyPot DNS blacklist integration */
            $honey = new HoneyPot($this->rampart);
            if (!$honey->check()) {
                $info = array(
                    Rampart::IP => $_SERVER['REMOTE_ADDR'],
                );
                if (!$this->rampart->checkWhiteList($info)) {
                    $honey->prevent();
                }
            }
        }

        if ($this->modx->getOption('rampart.denyaccess', null, false)) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $result = array(
                Rampart::STATUS => Rampart::STATUS_OK,
                Rampart::REASON => '',
                Rampart::IP => $ip,
                Rampart::HOSTNAME => gethostbyaddr($ip),
                Rampart::EMAIL => '',
                Rampart::USERNAME => '',
                Rampart::USER_AGENT => $_SERVER['HTTP_USER_AGENT'],
            );
            if (!$this->rampart->checkWhiteList($result)) {
                $result = $this->rampart->checkBanList($result);
            }
            if ($result[Rampart::STATUS] == Rampart::STATUS_BANNED) {
                if ($this->sp['modx3']) {
                    $this->banV3($result);
                } else {
                    $this->banV2($result);
                }
            }
        }
    }

    private function banV2(array $result)
    {
        $threshold = $this->modx->getOption('rampart.denyaccess.threshold', null, 5);
        $banCount = $this->modx->getCount('rptBanMatch', array('ban' => $result[Rampart::BAN]));
        if (($threshold > 1) && ($banCount >= $threshold)) {
            $this->respondError();
        }
    }
    private function banV3(array $result)
    {
        $threshold = $this->modx->getOption('rampart.denyaccess.threshold', null, 5);
        $banCount = $this->modx->getCount(\Rampart\Model\BanMatch::class, array('ban' => $result[Rampart::BAN]));
        if (($threshold > 1) && ($banCount >= $threshold)) {
            $this->respondError();
        }
    }

    private function respondError()
    {
        @session_write_close();
        header('HTTP/1.1 403 Forbidden');
        $message = '<p>Sorry, you have been banned. If you feel this is in error, please contact the administrator of this site.</p>';
        echo "<html>\n<head>\n<title>Access Denied</title>\n</head>\n<body>\n" . $message . "\n</body>\n</html>";
        exit();
    }
}
