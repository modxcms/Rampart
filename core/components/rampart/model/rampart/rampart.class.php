<?php

require_once dirname(__FILE__, 3) . '/vendor/autoload.php';

use Rampart\Rampart as RampartBase;
use Rampart\v2\Controller\Request;

class Rampart extends RampartBase
{
    public function addPackage()
    {
        $this->modx->addPackage('rampart', $this->config['modelPath']);
    }

    /**
     * Initializes modExtra into different contexts.
     *
     * @access public
     * @param string $ctx The context to load. Defaults to web.
     * @return string
     */
    public function initialize($ctx = 'web')
    {
        switch ($ctx) {
            case 'mgr':
                $this->request = new Request($this);
                return $this->request->handleRequest();
        }
        return '';
    }

    /**
     *
     * @param array $result
     * @return array
     */
    public function checkBanList($result)
    {
        $boomIp = explode('.', $result[RampartBase::IP]);

        /* build spam checking query */
        $c = $this->modx->newQuery('rptBan');
        $c->select($this->modx->getSelectColumns('rptBan', 'rptBan'));
        $c->select(array(
            'IF("'.$result[RampartBase::USERNAME].'" LIKE `rptBan`.`username`,1,0) AS `username_match`',
            'IF("'.$result[RampartBase::EMAIL].'" LIKE `rptBan`.`email`,1,0) AS `email_match`',
            'IF("'.$result[RampartBase::HOSTNAME].'" LIKE `rptBan`.`hostname`,1,0) AS `hostname_match`',
            'IF((('.$boomIp[0].' BETWEEN `rptBan`.`ip_low1` AND `rptBan`.`ip_high1`)
             AND ('.$boomIp[1].' BETWEEN `rptBan`.`ip_low2` AND `rptBan`.`ip_high2`)
             AND ('.$boomIp[2].' BETWEEN `rptBan`.`ip_low3` AND `rptBan`.`ip_high3`)
             AND ('.$boomIp[3].' BETWEEN `rptBan`.`ip_low4` AND `rptBan`.`ip_high4`)),1,0) AS `ip_match`',
        ));
        if (!empty($result[RampartBase::USERNAME])) {
            $c->orCondition(array(
                '"'.$result[RampartBase::USERNAME].'" LIKE rptBan.username',
            ), null, 2);
        }
        if (!empty($result[RampartBase::EMAIL])) {
            $c->orCondition(array(
                '"'.$result[RampartBase::EMAIL].'" LIKE rptBan.email',
            ), null, 2);
        }
        $c->orCondition(array(
            '"'.$result[RampartBase::HOSTNAME].'" LIKE rptBan.hostname',
        ), null, 2);
        $c->orCondition(array(
            '(('.$boomIp[0].' BETWEEN `rptBan`.`ip_low1` AND `rptBan`.`ip_high1`)
            AND ('.$boomIp[1].' BETWEEN `rptBan`.`ip_low2` AND `rptBan`.`ip_high2`)
            AND ('.$boomIp[2].' BETWEEN `rptBan`.`ip_low3` AND `rptBan`.`ip_high3`)
            AND ('.$boomIp[3].' BETWEEN `rptBan`.`ip_low4` AND `rptBan`.`ip_high4`))'
        ), null, 2);
        $c->where(array(
            'active' => true,
        ));
        $c->andCondition(array(
            'expireson:>' => time(),
            'OR:expireson:IS' => null,
            'OR:expireson:=' => '',
        ), null, 3);

        $bans = $this->modx->getCollection('rptBan', $c);
        if (count($bans)) {
            foreach ($bans as $ban) {
                $result[RampartBase::BAN] = $ban->get('id');
                $result[RampartBase::MATCH_FIELDS] = array();

                if ($ban->get('ip_match')) {
                    $result[RampartBase::MATCH_FIELDS]['ip'] = $result[RampartBase::IP];
                }
                if ($ban->get('username_match')) {
                    $result[RampartBase::MATCH_FIELDS]['username'] = $result[RampartBase::USERNAME];
                }
                if ($ban->get('hostname_match')) {
                    $result[RampartBase::MATCH_FIELDS]['hostname'] = $result[RampartBase::HOSTNAME];
                }
                if ($ban->get('email_match')) {
                    $result[RampartBase::MATCH_FIELDS]['email'] = $result[RampartBase::EMAIL];
                }
            }
            $result[RampartBase::REASON] = 'Manual Ban Match';
            $result[RampartBase::STATUS] = RampartBase::STATUS_BANNED;
        }
        return $result;
    }


    public function runStopForumSpamChecks(array $result)
    {
        /* Run StopForumSpam checks */
        $sfspam = new \Rampart\v2\StopForumSpam($this->modx);
        $spamResult = $sfspam->check(
            $result[RampartBase::IP],
            $result[RampartBase::EMAIL],
            $result[RampartBase::USERNAME]
        );
        if (!empty($spamResult)) {
            if (in_array('Ip', $spamResult) && in_array('Username', $spamResult)) {
                /**
                 * If ip AND username match, moderate user
                 */
                $result[RampartBase::STATUS] = RampartBase::STATUS_MODERATED;
                $result[RampartBase::REASON] = 'ipusername';
            } elseif (in_array('Email', $spamResult)) {
                /**
                 * Moderate users who match the email
                 */
                $result[RampartBase::STATUS] = RampartBase::STATUS_MODERATED;
                $result[RampartBase::REASON] = 'email';
            } elseif (in_array('Ip', $spamResult)) {
                $threshold = $this->modx->getOption(
                    'rampart.sfs_ipban_threshold',
                    null,
                    25
                ); /* threshold of reported times by SFS */
                $expiration = $this->modx->getOption(
                    'rampart.sfs_ipban_expiration',
                    null,
                    30
                ); /* # of days to ban */
                if ($threshold > 0) {
                    /**
                     * If the IP of the spammer shows up past our threshold
                     * of frequency times that StopForumSpam reports as,
                     * add a single-ip ban for a certain amount of time
                     */
                    $ipResult = $sfspam->check($result[RampartBase::IP]);
                    if (!empty($ipResult)) {
                        $ips = $sfspam->responseXml;
                        $frequency = (int)$ips->frequency;
                        if ($frequency >= $threshold) {
                            $result[RampartBase::STATUS] = RampartBase::STATUS_BANNED;
                            $result[RampartBase::REASON] = 'sfsip';
                            $result[RampartBase::DESCRIPTION] = 'StopForumSpam IP Ban';
                            $result[RampartBase::EXPIRATION] = $expiration;
                            $result[RampartBase::SERVICE] = 'stopforumspam';
                        }
                    }
                }
            }
        }
        return $result;
    }
    public function runProjectHoneyPotChecks(array $result) : array
    {
        if (empty($this->honey)) {
            $this->honey = new \Rampart\HoneyPot($this);
        }
        if (!$this->honey->check()) {
            $result['response'] = $this->honey->values;
            $result[RampartBase::STATUS] = RampartBase::STATUS_BANNED;
            $result[RampartBase::SERVICE] = 'projecthoneypot';
            $result[RampartBase::REASON] = 'Suspicious';
            $result[RampartBase::MATCH_FIELDS] = array(RampartBase::MATCH_IP => $result[RampartBase::IP]);
            if (!empty($this->honey->values['comment_spammer'])) {
                $result[RampartBase::REASON] = 'HoneyPot: Comment Spammer';
            } elseif (!empty($this->honey->values['harvester'])) {
                $result[RampartBase::REASON] = 'HoneyPot: Harvester';
            }
            $result[RampartBase::EXPIRATION] = $this->modx->getOption(
                'rampart.honeypot.ban_expiration',
                $this->config,
                30
            );
        }
        return $result;
    }

    /**
     * Check to see if an IP is on the WhiteList
     *
     * @param array $result
     * @return bool True if found on the WhiteList
     */
    public function checkWhiteList(array $result = array()) : bool
    {
        $c = $this->modx->newQuery('rptWhiteList');
        $c->where(array(
            'ip' => $result[RampartBase::IP],
            'active' => true,
        ));
        $count = $this->modx->getCount('rptWhiteList', $c);
        return $count > 0;
    }
    /**
     * Add a ban to the banlist
     *
     * @param array $result
     * @return boolean
     *
     */
    public function addBan(array $result = array()) : bool
    {
        if (empty($result[RampartBase::EXPIRATION])) {
            $result[RampartBase::EXPIRATION] = 30;
        }

        /* if specifying an existing ban */
        if (!empty($result[RampartBase::BAN])) {
            $ban = $this->modx->getObject('rptBan', $result[RampartBase::BAN]);
        }
        /* otherwise we'll try and grab it from the IP */
        if (empty($ban)) {
            $ban = $this->modx->getObject('rptBan', array(
                'ip' => $result[RampartBase::IP],
            ));
        }
        /* and finally, if no matches, create a new ban */
        if (empty($ban)) {
            $ban = $this->modx->newObject('rptBan');
            $ban->set('createdon', time());
            $ban->set('active', true);
            $boomIp = explode('.', $result[RampartBase::IP]);
            $ban->set('ip_low1', $boomIp[0]);
            $ban->set('ip_high1', $boomIp[0]);
            $ban->set('ip_low2', $boomIp[1]);
            $ban->set('ip_high2', $boomIp[1]);
            $ban->set('ip_low3', $boomIp[2]);
            $ban->set('ip_high3', $boomIp[2]);
            $ban->set('ip_low4', $boomIp[3]);
            $ban->set('ip_high4', $boomIp[3]);
            $ban->set('matches', 1);
            $future = time() + ($result[RampartBase::EXPIRATION] * 24 * 60 * 60);
            $ban->set('expireson', $future);
        } else {
            $matches = (int)$ban->get('matches') + 1;
            $ban->set('matches', $matches);
        }

        /* now update IP, last active, store latest data, etc */
        if (!empty($result[RampartBase::REASON])) {
            $ban->set('reason', $result[RampartBase::REASON]);
        }
        $ban->set('ip', $result[RampartBase::IP]);
        $lastActive = time();
        $ban->set('last_activity', $lastActive);
        $ban->set('data', $result);
        $ban->set('service', !empty($result[RampartBase::SERVICE]) ? $result[RampartBase::SERVICE] : 'manual');
        if ($ban->save()) {
            /* now create match record */
            $match = $this->modx->newObject('rptBanMatch');
            $match->set('ban', $ban->get('id'));
            $match->set('ip', $result[RampartBase::IP]);
            $match->set('hostname', !empty($result[RampartBase::HOSTNAME]) ? $result[RampartBase::HOSTNAME] : '');

            $username = !empty($result[RampartBase::USERNAME]) ?
                $result[RampartBase::USERNAME] : $this->modx->user->get('username');
            $match->set('username', $username);
            $match->set('email', !empty($result[RampartBase::EMAIL]) ? $result[RampartBase::EMAIL] : '');
            $match->set('useragent', !empty($result[RampartBase::USER_AGENT]) ? $result[RampartBase::USER_AGENT] : '');

            if (!empty($result[RampartBase::MATCH_FIELDS])) {
                $fields = is_array($result[RampartBase::MATCH_FIELDS]) ?
                    $result[RampartBase::MATCH_FIELDS] : explode(',', $result[RampartBase::MATCH_FIELDS]);
            } else {
                $fields = array();
            }
            if (!empty($fields['ip'])) {
                $match->set('ip_match', $fields['ip']);
            }
            if (!empty($fields['username'])) {
                $match->set('username_match', $fields['username']);
            }
            if (!empty($fields['hostname'])) {
                $match->set('hostname_match', $fields['hostname']);
            }
            if (!empty($fields['email'])) {
                $match->set('email_match', $fields['email']);
            }

            $match->set('resource', ($this->modx->resource) ? $this->modx->resource->get('id') : 0);
            $match->set('data', $result);
            $match->set('service', !empty($result[RampartBase::SERVICE]) ? $result[RampartBase::SERVICE] : 'manual');
            $match->set('notes', !empty($result[RampartBase::NOTES]) ? $result[RampartBase::NOTES] : '');
            $match->set('createdon', time());
            $match->set('reason', !empty($result[RampartBase::REASON]) ? $result[RampartBase::REASON] : '');

            if ($match->save()) {
                /* if any field matches, store here */
                foreach ($fields as $field => $value) {
                    $bmf = $this->modx->newObject('rptBanMatchField');
                    $bmf->set('ban', $ban->get('id'));
                    $bmf->set('ban_match', $match->get('id'));
                    $bmf->set('field', $field);
                    $bmf->save();
                }
            }
        }
        return true;
    }
}
