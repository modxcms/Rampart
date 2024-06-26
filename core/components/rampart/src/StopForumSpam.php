<?php

namespace Rampart;

use MODX\Revolution\Rest\modRest;

class StopForumSpam
{
    public $response = null;
    public $modx = null;
    public function __construct(&$modx, array $config = array())
    {
        $this->modx =& $modx;

        $this->config = array_merge(array(
            'host' => 'http://www.stopforumspam.com/',
            'path' => 'api',
            'method' => 'GET',
        ), $config);
    }

    /**
     * Check for spammer
     *
     * @access public
     * @param string $ip
     * @param string $email
     * @param string $username
     * @return array An array of errors
     */
    public function check(string $ip = '', string $email = '', string $username = '') : array
    {
        $params = array();
        if (!empty($ip)) {
            if (in_array($ip, array('127.0.0.1','::1','0.0.0.0'))) {
                $ip = '72.179.10.158';
            }
            $params['ip'] = $ip;
        }
        if (!empty($email)) {
            $params['email'] = $email;
        }
        if (!empty($username)) {
            $params['username'] = $username;
        }

        $response = $this->request($params);
        if (empty($response)) {
            return array();
        }
        $i = 0;
        $errors = array();
        foreach ($response['appears'] as $result) {
            if ($result == 'yes') {
                $errors[] = ucfirst($response['type'][$i]);
            }
            $i++;
        }
        return $errors;
    }

    /**
     * Make a request to stopforumspam.com
     *
     * @access public
     * @param array $params An array of parameters to send
     * @return mixed The return SimpleXML object, or false if none
     */
    public function request(array $params = array())
    {
        $this->getClient();

        $this->response = $this->modx->rest->request(
            $this->config['method'],
            $this->config['host'] . $this->config['path'],
            $params
        );
        return $this->response->process();
    }

    /**
     * Get the REST Client
     *
     * @access private
     * @return modRest
     */
    private function getClient(): modRest
    {
        if (empty($this->modx->rest)) {
            $this->modx->rest = new modRest($this->modx);
        }
        return $this->modx->rest;
    }
}
