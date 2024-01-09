<?php

namespace Rampart\v2\Processors\WhiteList;

class Remove extends \modObjectRemoveProcessor
{
    public $classKey = 'rptWhiteList';
    public $objectType = 'rampart.whitelist';
    public $languageTopics = array('rampart:default');
}
