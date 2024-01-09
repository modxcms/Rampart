<?php

namespace Rampart\v2\Processors\Matches;

class GetList extends \modObjectGetListProcessor
{
    public $classKey = 'rptBanMatch';
    public $objectType = 'rampart.match';
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'DESC';
    public $languageTopics = array('rampart:default');
    public function initialize()
    {
        $initialized = parent::initialize();
        $this->setDefaultProperties(array(
            'search' => false,
            'ban' => false,
        ));
        return $initialized;
    }

    public function prepareQueryBeforeCount(\xPDOQuery $c)
    {
        $c->leftJoin('modResource', 'Resource');
        $ban = $this->getProperty('ban');
        if (!empty($ban)) {
            $c->where(array(
                'rptBanMatch.ban' => $ban,
            ));
        }
        $search = $this->getProperty('search');
        if (!empty($search)) {
            $c->where(array(
                'ip:LIKE' => '%'.$search.'%',
                'OR:hostname:LIKE' => '%'.$search.'%',
                'OR:email:LIKE' => '%'.$search.'%',
                'OR:username:LIKE' => '%'.$search.'%',
                'OR:useragent:LIKE' => '%'.$search.'%',
            ), null, 2);
        }
        return $c;
    }

    public function prepareQueryAfterCount(\xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns('rptBanMatch', 'rptBanMatch'));
        $c->select($this->modx->getSelectColumns('modResource', 'Resource', '', array('pagetitle')));
        return $c;
    }

    protected function getArrayAsList($array = array())
    {
        if (empty($array)) {
            return '';
        }
        $out = '<ul>'."\n";
        foreach ($array as $key => $elem) {
            $out .= '<li>';
            if (is_array($elem)) {
                $out .= $this->getArrayAsList($elem);
            } else {
                $out .= '<b>'.$key.'</b>: '.$elem;
            }
            $out .= '</li>'."\n";
        }
        $out .= '</ul>'."\n";
        return $out;
    }

    public function prepareRow(\xPDOObject $object)
    {
        $objectArray = $object->toArray();
        $objectArray['createdon'] = strftime('%b %d, %Y %I:%M %p', strtotime($object->get('createdon')));
        $objectArray['pagetitle'] = !empty($objectArray['pagetitle']) ?
            $objectArray['pagetitle'].' ('.$objectArray['resource'].')' : '';

        if (!empty($objectArray['data'])) {
            $objectArray['data_formatted'] = $this->getArrayAsList($objectArray['data']);
        }
        return $objectArray;
    }
}
