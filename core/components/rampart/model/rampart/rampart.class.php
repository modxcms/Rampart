<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/autoload.php';

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
}
