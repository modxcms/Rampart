<?php
namespace Rampart\Model;

use xPDO\xPDO;

/**
 * Class BanMatch
 *
 * @property integer $ban
 * @property string $reason
 * @property string $username
 * @property string $username_match
 * @property string $hostname
 * @property string $hostname_match
 * @property string $email
 * @property string $email_match
 * @property string $ip
 * @property string $ip_match
 * @property string $useragent
 * @property string $createdon
 * @property integer $resource
 * @property string $notes
 * @property array $data
 * @property string $service
 *
 * @property \BanMatchField[] $Fields
 *
 * @package Rampart\Model
 */
class BanMatch extends \xPDO\Om\xPDOSimpleObject
{
}
