<?php


class Cm_Saas_Model_Resource_Db extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_setResource('core', 'saas');
        $this->_init('saas/db', 'db_id');
    }

}
