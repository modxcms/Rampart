<?php

namespace Rampart\Processors\WhiteList;

class Remove extends \MODX\Revolution\Processors\Model\RemoveProcessor
{
    public $classKey = \Rampart\Model\WhiteList::class;
    public $objectType = 'rampart.whitelist';
    public $languageTopics = array('rampart:default');
}
