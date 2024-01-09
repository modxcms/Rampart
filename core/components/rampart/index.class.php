<?php

/**
 * @package rampart
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';
class ControllersIndexManagerController extends modExtraManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}

abstract class RampartManagerController extends modManagerController
{
    public $rampart;
    public function initialize()
    {
        if (empty($this->modx->version)) {
            $this->modx->getVersionData();
        }
        if ($this->modx->version['version'] < 3) {
            $corePath = $this->modx->getOption(
                'rampart.core_path',
                null,
                $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/rampart/'
            );
            $this->rampart = $this->modx->getService(
                'rampart',
                'rampart',
                $corePath . 'model/rampart/',
                [
                    'core_path' => $corePath
                ]
            );
        } else {
            $this->rampart = new \Rampart\Rampart($this->modx, []);
        }
        $this->rampart->config['modx3'] = ($this->modx->version['version'] >= 3);
        if ($this->rampart->config['modx3']) {
            $this->rampart->config['connectorUrl'] = $this->modx->config['connector_url'];
        }

        $this->addJavascript($this->rampart->config['jsUrl'].'rampart.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Rampart.config = '.$this->modx->toJSON($this->rampart->config).';
            Rampart.config.connector_url = "'.$this->rampart->config['connectorUrl'].'";
        });
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics()
    {
        return array('rampart:default');
    }
    public function checkPermissions()
    {
        return true;
    }
}
