<?php
namespace Rampart\Model;

use xPDO\xPDO;

/**
 * Class Ban
 *
 * @property string $reason
 * @property string $ip
 * @property integer $ip_low1
 * @property integer $ip_high1
 * @property integer $ip_low2
 * @property integer $ip_high2
 * @property integer $ip_low3
 * @property integer $ip_high3
 * @property integer $ip_low4
 * @property integer $ip_high4
 * @property string $hostname
 * @property string $email
 * @property string $username
 * @property integer $matches
 * @property string $createdon
 * @property string $editedon
 * @property integer $editedby
 * @property string $expireson
 * @property string $notes
 * @property boolean $active
 * @property string $last_activity
 * @property array $data
 * @property string $service
 *
 * @property \BanMatch[] $Matches
 * @property \BanMatchField[] $Fields
 *
 * @package Rampart\Model
 */
class Ban extends \xPDO\Om\xPDOSimpleObject
{
    public function set($k, $v = null, $vType = '')
    {
        switch ($k) {
            case 'ip':
                $ipex = explode('.', $v);
                for ($i=0; $i<4; $i++) {
                    $n = $i+1;
                    if (!isset($ipex[$i])) {
                        $this->set('ip_low'.$n, 0);
                        $this->set('ip_high'.$n, 0);
                    } elseif (strpos($ipex[$i], '-') !== false) {
                        $ipr = explode('-', $ipex[$i]);
                        $this->set('ip_low'.$n, $ipr[0]);
                        $this->set('ip_high'.$n, $ipr[1]);
                    } elseif ($ipex[$i] == '*') {
                        $this->set('ip_low'.$n, 0);
                        $this->set('ip_high'.$n, 255);
                    } else {
                        $this->set('ip_low'.$n, $ipex[$i]);
                        $this->set('ip_high'.$n, $ipex[$i]);
                    }
                }
                break;
        }
        return parent :: set($k, $v, $vType);
    }

    public function get($k, $format = null, $formatTemplate = null)
    {
        switch ($k) {
            case 'ip':
                $ip = '';
                $i = 1;
                for ($i=1; $i<5; $i++) {
                    $ip .= '.';
                    $block = $this->get('ip_low'.$i) == $this->get('ip_high'.$i) ?
                        $this->get('ip_low'.$i) : $this->get('ip_low'.$i).'-'.$this->get('ip_high'.$i);
                    if ($block == '0-255' && !empty($block)) {
                        $block = '*';
                    }
                    $ip .= $block;
                }
                $v = trim($ip, '.');
                if ($v == '0.0.0.0') {
                    $v = '';
                }
                break;
            default:
                $v = parent::get($k, $format, $formatTemplate);
                break;
        }
        return $v;
    }
}
