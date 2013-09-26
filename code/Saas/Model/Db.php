<?php

/**
 * Contains connection information for a database resource
 *
 * @method Cm_Saas_Model_Resource_Db getResource()
 * @method string getName()
 * @method Cm_Saas_Model_Db setName(string $value)
 * @method string getPrefix()
 * @method Cm_Saas_Model_Db setPrefix(string $value)
 * @method string getConnectionXml()
 * @method Cm_Saas_Model_Db setConnectionXml(string $value)
 */
class Cm_Saas_Model_Db extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('saas/db');
    }

    /**
     * Sample:
     *   <host/>
     *   <username/>
     *   <password/>
     *   <dbname/>
     *   <model>mysql4</model>
     *   <initStatements>SET NAMES utf8</initStatements>
     *   <type>pdo_mysql</type>
     *   <active>1</active>
     */
    public function getConnectionConfig($dbName)
    {
        static $config;
        if ( ! $config) {
            $config = new Varien_Simplexml_Element('<root></root>');
            $config->model = 'mysql4';
            $config->initStatements = 'SET NAMES utf8';
            $config->type = 'pdo_mysql';
            $config->active = '1';
            $xml = new Varien_Simplexml_Element('<root>'.$this->getConnectionXml().'</root>');
            $config->extend($xml, TRUE);
            $config->dbname = $this->getPrefix().$dbName;
        }
        return $config;
    }

}
