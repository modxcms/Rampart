<?php

namespace Rampart;

use Rampart\Controller\Request;
use Rampart\Model\Ban;
use Rampart\Model\BanMatch;
use Rampart\Model\BanMatchField;
use Rampart\Model\WhiteList;

class Rampart
{
    const REASON = 'reason';
    const STATUS = 'status';
    const DESCRIPTION = 'description';
    const IP = 'ip';
    const HOSTNAME = 'hostname';
    const EMAIL = 'email';
    const USERNAME = 'username';
    const USER_AGENT = 'user_agent';
    const EXPIRATION = 'expiration';
    const SERVICE = 'service';
    const NOTES = '';
    const BAN = 'ban';
    const STATUS_OK = 'ok';
    const STATUS_BANNED = 'banned';
    const STATUS_MODERATED = 'moderated';
    const MATCH_IP = 'match_ip';
    const MATCH_USERNAME = 'match_username';
    const MATCH_HOSTNAME = 'match_hostname';
    const MATCH_EMAIL = 'match_email';
    const MATCH_FIELDS = 'match_fields';

    public $request;
    public $modx;
    public $honey;
    public $config = array();

    public function __construct(&$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption(
            'rampart.core_path',
            $config,
            $this->modx->getOption('core_path').'components/rampart/'
        );
        $assetsUrl = $this->modx->getOption(
            'rampart.assets_url',
            $config,
            $this->modx->getOption('assets_url').'components/rampart/'
        );
        $connectorUrl = $assetsUrl.'connector.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl.'css/',
            'jsUrl' => $assetsUrl.'js/',
            'imagesUrl' => $assetsUrl.'images/',

            'connectorUrl' => $connectorUrl,

            'corePath' => $corePath,
            'modelPath' => $corePath.'model/',
            'chunksPath' => $corePath.'elements/chunks/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath.'elements/snippets/',
            'processorsPath' => $corePath.'processors/',
            'controllersPath' => $corePath.'controllers/',
            'templatesPath' => $corePath.'templates/',

            'salt' => $this->modx->getOption('rampart.salt', $config, 'sieg3thec4stle'),
        ), $config);

        //$this->modx->addPackage('rampart',$this->config['modelPath']);
        $this->addPackage();

        $this->modx->lexicon->load('rampart:default');
    }

    public function addPackage()
    {
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
     * Run the spam checks
     *
     * @param string $username
     * @param string $email
     * @return array
     */
    public function check($username = '', $email = ''): array
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip == '::1') {
            $ip = '72.177.93.127';
        }
        /* demo spammer data */
        //$ip = '109.230.213.121';
        //$username = 'RyanHG';
        //$email = 'yumunter@fmailer.net';

        $result = array(
            Rampart::STATUS => Rampart::STATUS_OK,
            Rampart::REASON => '',
            Rampart::IP => $ip,
            Rampart::HOSTNAME => gethostbyaddr($ip),
            Rampart::EMAIL => $email,
            Rampart::USERNAME => $username,
            Rampart::USER_AGENT => $_SERVER['HTTP_USER_AGENT'],
        );

        if (!$this->checkWhiteList($result)) {
            /* check Rampart ban list */
            $result = $this->checkBanList($result);

            /* Run StopForumSpam checks */
            $result = $this->runStopForumSpamChecks($result);

            /* Run ProjectHoneyPot checks */
            if ($this->modx->getOption('rampart.honeypot.enabled', null, false)) {
                $result = $this->runProjectHoneyPotChecks($result);
            }

            if (!empty($result[Rampart::STATUS]) && $result[Rampart::STATUS] == Rampart::STATUS_BANNED) {
                $this->addBan($result);
            }
        }
        return $result;
    }

    /**
     *
     * @param array $result
     * @return array
     */
    public function checkBanList($result)
    {
        $boomIp = explode('.', $result[Rampart::IP]);

        /* build spam checking query */
        $c = $this->modx->newQuery(Ban::class);
        $c->select($this->modx->getSelectColumns(Ban::class, 'rptBan'));
        $c->select(array(
            'IF("'.$result[Rampart::USERNAME].'" LIKE `rptBan`.`username`,1,0) AS `username_match`',
            'IF("'.$result[Rampart::EMAIL].'" LIKE `rptBan`.`email`,1,0) AS `email_match`',
            'IF("'.$result[Rampart::HOSTNAME].'" LIKE `rptBan`.`hostname`,1,0) AS `hostname_match`',
            'IF((('.$boomIp[0].' BETWEEN `rptBan`.`ip_low1` AND `rptBan`.`ip_high1`)
             AND ('.$boomIp[1].' BETWEEN `rptBan`.`ip_low2` AND `rptBan`.`ip_high2`)
             AND ('.$boomIp[2].' BETWEEN `rptBan`.`ip_low3` AND `rptBan`.`ip_high3`)
             AND ('.$boomIp[3].' BETWEEN `rptBan`.`ip_low4` AND `rptBan`.`ip_high4`)),1,0) AS `ip_match`',
        ));
        if (!empty($result[Rampart::USERNAME])) {
            $c->orCondition(array(
                '"'.$result[Rampart::USERNAME].'" LIKE rptBan.username',
            ), null, 2);
        }
        if (!empty($result[Rampart::EMAIL])) {
            $c->orCondition(array(
                '"'.$result[Rampart::EMAIL].'" LIKE rptBan.email',
            ), null, 2);
        }
        $c->orCondition(array(
            '"'.$result[Rampart::HOSTNAME].'" LIKE rptBan.hostname',
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

        $bans = $this->modx->getCollection(\Rampart\Model\Ban::class, $c);
        if (count($bans)) {
            foreach ($bans as $ban) {
                $result[Rampart::BAN] = $ban->get('id');
                $result[Rampart::MATCH_FIELDS] = array();

                if ($ban->get('ip_match')) {
                    $result[Rampart::MATCH_FIELDS]['ip'] = $result[Rampart::IP];
                }
                if ($ban->get('username_match')) {
                    $result[Rampart::MATCH_FIELDS]['username'] = $result[Rampart::USERNAME];
                }
                if ($ban->get('hostname_match')) {
                    $result[Rampart::MATCH_FIELDS]['hostname'] = $result[Rampart::HOSTNAME];
                }
                if ($ban->get('email_match')) {
                    $result[Rampart::MATCH_FIELDS]['email'] = $result[Rampart::EMAIL];
                }
            }
            $result[Rampart::REASON] = 'Manual Ban Match';
            $result[Rampart::STATUS] = Rampart::STATUS_BANNED;
        }
        return $result;
    }


    public function runStopForumSpamChecks(array $result)
    {
        /* Run StopForumSpam checks */
        $sfspam = new StopForumSpam($this->modx);
        $spamResult = $sfspam->check($result[Rampart::IP], $result[Rampart::EMAIL], $result[Rampart::USERNAME]);
        if (!empty($spamResult)) {
            if (in_array('Ip', $spamResult) && in_array('Username', $spamResult)) {
                /**
                 * If ip AND username match, moderate user
                 */
                $result[Rampart::STATUS] = Rampart::STATUS_MODERATED;
                $result[Rampart::REASON] = 'ipusername';
            } elseif (in_array('Email', $spamResult)) {
                /**
                 * Moderate users who match the email
                 */
                $result[Rampart::STATUS] = Rampart::STATUS_MODERATED;
                $result[Rampart::REASON] = 'email';
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
                    $ipResult = $sfspam->check($result[Rampart::IP]);
                    if (!empty($ipResult)) {
                        $ips = $sfspam->responseXml;
                        $frequency = (int)$ips->frequency;
                        if ($frequency >= $threshold) {
                            $result[Rampart::STATUS] = Rampart::STATUS_BANNED;
                            $result[Rampart::REASON] = 'sfsip';
                            $result[Rampart::DESCRIPTION] = 'StopForumSpam IP Ban';
                            $result[Rampart::EXPIRATION] = $expiration;
                            $result[Rampart::SERVICE] = 'stopforumspam';
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Run checks for Project Honey Pot
     * @param array $result
     * @return array
     */
    public function runProjectHoneyPotChecks(array $result) : array
    {
        if (empty($this->honey)) {
            $this->honey = new HoneyPot($this);
        }
        if (!$this->honey->check()) {
            $result['response'] = $this->honey->values;
            $result[Rampart::STATUS] = Rampart::STATUS_BANNED;
            $result[Rampart::SERVICE] = 'projecthoneypot';
            $result[Rampart::REASON] = 'Suspicious';
            $result[Rampart::MATCH_FIELDS] = array(Rampart::MATCH_IP => $result[Rampart::IP]);
            if (!empty($this->honey->values['comment_spammer'])) {
                $result[Rampart::REASON] = 'HoneyPot: Comment Spammer';
            } elseif (!empty($this->honey->values['harvester'])) {
                $result[Rampart::REASON] = 'HoneyPot: Harvester';
            }
            $result[Rampart::EXPIRATION] = $this->modx->getOption('rampart.honeypot.ban_expiration', $this->config, 30);
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
        $c = $this->modx->newQuery(WhiteList::class);
        $c->where(array(
            'ip' => $result[Rampart::IP],
            'active' => true,
        ));
        $count = $this->modx->getCount(WhiteList::class, $c);
        return $count > 0;
    }

    /**
     * Generate a random key password
     *
     * @param int $length
     * @return string
     */
    public function generatePassword($length = 8) : string
    {
        $pword = '';
        $charmap = '0123456789bcdfghjkmnpqrstvwxyz';
        $i = 0;
        while ($i < $length) {
            $char = substr($charmap, rand(0, strlen($charmap)-1), 1);
            if (!strstr($pword, $char)) {
                $pword .= $char;
                $i++;
            }
        }
        return $pword;
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
        if (empty($result[Rampart::EXPIRATION])) {
            $result[Rampart::EXPIRATION] = 30;
        }

        /* if specifying an existing ban */
        if (!empty($result[Rampart::BAN])) {
            $ban = $this->modx->getObject(Ban::class, $result[Rampart::BAN]);
        }
        /* otherwise we'll try and grab it from the IP */
        if (empty($ban)) {
            $ban = $this->modx->getObject(Ban::class, array(
                'ip' => $result[Rampart::IP],
            ));
        }
        /* and finally, if no matches, create a new ban */
        if (empty($ban)) {
            $ban = $this->modx->newObject(Ban::class);
            $ban->set('createdon', time());
            $ban->set('active', true);
            $boomIp = explode('.', $result[Rampart::IP]);
            $ban->set('ip_low1', $boomIp[0]);
            $ban->set('ip_high1', $boomIp[0]);
            $ban->set('ip_low2', $boomIp[1]);
            $ban->set('ip_high2', $boomIp[1]);
            $ban->set('ip_low3', $boomIp[2]);
            $ban->set('ip_high3', $boomIp[2]);
            $ban->set('ip_low4', $boomIp[3]);
            $ban->set('ip_high4', $boomIp[3]);
            $ban->set('matches', 1);
            $future = time() + ($result[Rampart::EXPIRATION] * 24 * 60 * 60);
            $ban->set('expireson', $future);
        } else {
            $matches = (int)$ban->get('matches') + 1;
            $ban->set('matches', $matches);
        }

        /* now update IP, last active, store latest data, etc */
        if (!empty($result[Rampart::REASON])) {
            $ban->set('reason', $result[Rampart::REASON]);
        }
        $ban->set('ip', $result[Rampart::IP]);
        $lastActive = time();
        $ban->set('last_activity', $lastActive);
        $ban->set('data', $result);
        $ban->set('service', !empty($result[Rampart::SERVICE]) ? $result[Rampart::SERVICE] : 'manual');
        if ($ban->save()) {
            /* now create match record */
            $match = $this->modx->newObject(BanMatch::class);
            $match->set('ban', $ban->get('id'));
            $match->set('ip', $result[Rampart::IP]);
            $match->set('hostname', !empty($result[Rampart::HOSTNAME]) ? $result[Rampart::HOSTNAME] : '');

            $username = !empty($result[Rampart::USERNAME]) ?
                $result[Rampart::USERNAME] : $this->modx->user->get('username');
            $match->set('username', $username);
            $match->set('email', !empty($result[Rampart::EMAIL]) ? $result[Rampart::EMAIL] : '');
            $match->set('useragent', !empty($result[Rampart::USER_AGENT]) ? $result[Rampart::USER_AGENT] : '');

            if (!empty($result[Rampart::MATCH_FIELDS])) {
                $fields = is_array($result[Rampart::MATCH_FIELDS]) ?
                    $result[Rampart::MATCH_FIELDS] : explode(',', $result[Rampart::MATCH_FIELDS]);
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
            $match->set('service', !empty($result[Rampart::SERVICE]) ? $result[Rampart::SERVICE] : 'manual');
            $match->set('notes', !empty($result[Rampart::NOTES]) ? $result[Rampart::NOTES] : '');
            $match->set('createdon', time());
            $match->set('reason', !empty($result[Rampart::REASON]) ? $result[Rampart::REASON] : '');

            if ($match->save()) {
                /* if any field matches, store here */
                foreach ($fields as $field => $value) {
                    $bmf = $this->modx->newObject(BanMatchField::class);
                    $bmf->set('ban', $ban->get('id'));
                    $bmf->set('ban_match', $match->get('id'));
                    $bmf->set('field', $field);
                    $bmf->save();
                }
            }
        }
        return true;
    }



    /**
     * Encrypts a string with a salted hash
     *
     * @access private
     * @param string $str The string to encrypt
     * @return An encrypted, salted hash
     */
    public function encrypt(string $str): string
    {
        $key = md5($this->config['salt']);
        $iv = substr(sha1(mt_rand()), 0, 16);

        $encrypted = openssl_encrypt(
            $str,
            'AES-256-CBC',
            $key,
            0,
            $iv
        );
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypts a string based upon the set hash
     *
     * @access private
     * @param string $str The string to decrypt
     * @return string A decrypted string
     */
    public function decrypt(string $str): string
    {
        $key = md5($this->config['salt']);

        list($encrypted_data, $iv) = explode('::', base64_decode($str), 2);

        return openssl_decrypt(
            $encrypted_data,
            'AES-256-CBC',
            $key,
            0,
            $iv
        );
    }
}
