<?php
/**
 * Rampart
 *
 * Copyright 2011 by Shaun McCormick <shaun@modx.com>
 *
 * Rampart is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Rampart is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Rampart; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package rampart
 */
/**
 * @package rampart
 * @subpackage processors
 */
class RampartBanDuplicateProcessor extends modObjectProcessor {
    public $classKey = 'rptBan';
    public $objectType = 'rampart.ban';
    public $languageTopics = array('rampart:default');

    public function initialize() {
        $id = $this->getProperty('id',false);
        if (empty($id)) {
            return $this->modx->lexicon('rampart.ban_err_ns');
        }
        $this->object = $this->modx->getObject($this->classKey,$id);
        if (empty($this->object)) { return $this->modx->lexicon('rampart.ban_err_nf'); }
        return true;
    }

    public function process() {
        /** @var rptBan $newBan */
        $newBan = $this->modx->newObject($this->classKey);
        $newBan->fromArray($this->object->toArray());
        $newBan->set('editedon',null);
        $newBan->set('editedby',0);
        $newBan->set('createdon',time());
        $newBan->set('active',0);

        if ($newBan->save() === false) {
            return $this->failure($this->modx->lexicon('rampart.ban_err_duplicate'));
        }

        return $this->success('',$newBan);
    }
}
return 'RampartBanDuplicateProcessor';