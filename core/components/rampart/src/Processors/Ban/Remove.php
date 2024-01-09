<?php

namespace Rampart\Processors\Ban;

class Remove extends \MODX\Revolution\Processors\Model\RemoveProcessor
{
    public $classKey = \Rampart\Model\Ban::class;
    public $objectType = 'rampart.ban';
    public $languageTopics = array('rampart:default');
}
