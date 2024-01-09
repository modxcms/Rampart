<?php

namespace Rampart\Elements\Snippet;

abstract class Snippet
{
    /**
     * A reference to the modX object.
     * @var modX $modx
     */
    public $modx = null;

    protected $rampart;

    /** @var array */
    protected $sp = [];

    public function __construct($rampart, array $scriptProperties)
    {
        $this->rampart =& $rampart;
        $this->modx =& $this->rampart->modx;
        $this->sp = $scriptProperties;
    }

    abstract public function run();

    protected function getOption($key, $default = null, $skipEmpty = true)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }
}
