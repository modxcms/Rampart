<?php

namespace Rampart\v2\Processors\Ban;

class Remove extends \modObjectRemoveProcessor
{
    public $classKey = 'rptBan';
    public $objectType = 'rampart.ban';
    public $languageTopics = array('rampart:default');
}
