<?php


class Cm_Saas_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{

    /**
     * Setup Connection
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_saasConn;

    /**
     * Initialize resource configurations, setup connection, etc
     *
     * @param string $resourceName the setup resource name
     */
    public function __construct($resourceName)
    {
        $config = Mage::getConfig();
        $this->_resourceName = $resourceName;
        $this->_resourceConfig = $config->getResourceConfig($resourceName);
        $connection = $config->getResourceConnectionConfig($resourceName);
        if ($connection) {
            $this->_connectionConfig = $connection;
        } else {
            $this->_connectionConfig = $config->getResourceConnectionConfig(self::DEFAULT_SETUP_CONNECTION);
        }

        $modName = (string)$this->_resourceConfig->setup->module;
        $this->_moduleConfig = $config->getModuleConfig($modName);
        $this->_conn = Mage::getSingleton('core/resource')->getConnection('core_setup');
    }

    /**
     * Get connection object
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection()
    {
        if (Mage::helper('saas')->getWebsite()) {
            return Mage::getSingleton('core/resource')->getConnection('saas_setup');
        }
        return $this->_conn;
    }

    /**
     * Get connection object
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getCoreConnection()
    {
        return $this->_conn;
    }

    /**
     * Get resource resource model for saas
     *
     * @return Mage_Core_Model_Resource_Resource
     */
    protected function _getResource()
    {
        static $_resources = [];
        if (Mage::helper('saas')->useDbPerWebsite() && Mage::helper('saas')->getWebsite()) {
            $currentAccountId = Mage::helper('saas')->getCurrentAccountId();
            if ( ! isset($_resources[$currentAccountId])) {
                $_resources[$currentAccountId] = Mage::getResourceModel('saas/resource');
            }
            return $_resources[$currentAccountId];
        }
        return parent::_getResource();
    }

    /**
     * @return Mage_Core_Model_Website[]
     */
    protected function _getSetupTargets()
    {
        return Mage::app()->getWebsites();
    }

    /**
     * Apply data updates to the system after upgrading.
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyDataUpdates()
    {
        $dataVer= $this->_getResource()->getDataVersion($this->_resourceName);
        $configVer = (string)$this->_moduleConfig->version;

        if (Mage::helper('saas')->useDbPerWebsite()) {
            foreach ($this->_getSetupTargets() as $website) {
                Mage::helper('saas')->setWebsite($website);
                if (Mage::helper('saas')->getWebsite()) {
                    parent::applyDataUpdates();
                }
            }
            Mage::helper('saas')->setWebsite(FALSE);
            if ( ! $dataVer || version_compare($dataVer, $configVer) == self::VERSION_COMPARE_LOWER) {
                $this->_getResource()->setDataVersion($this->_resourceName, $configVer);
            }
        } else {
            parent::applyDataUpdates();
        }

        return $this;
    }

    /**
     * Apply module resource install, upgrade and data scripts
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyUpdates()
    {
        $dbVer = $this->_getResource()->getDbVersion($this->_resourceName);
        $configVer = (string)$this->_moduleConfig->version;

        if (Mage::helper('saas')->useDbPerWebsite()) {
            foreach ($this->_getSetupTargets() as $website) {
                Mage::helper('saas')->setWebsite($website);
                if (Mage::helper('saas')->getWebsite()) {
                    parent::applyDataUpdates();
                }
            }
            Mage::helper('saas')->setWebsite(FALSE);
            if ( ! $dbVer || version_compare($dbVer, $configVer) == self::VERSION_COMPARE_LOWER) {
                $this->_getResource()->setDataVersion($this->_resourceName, $configVer);
            }
        } else {
            parent::applyDataUpdates();
        }

        return $this;
    }


/******************* CONFIG *****************/

    /**
     * Save configuration data
     *
     * @param string $path
     * @param string $value
     * @param int|string $scope
     * @param int $scopeId
     * @param int $inherit
     * @return Mage_Core_Model_Resource_Setup
     */
    public function setConfigData($path, $value, $scope = 'default', $scopeId = 0, $inherit=0)
    {
        $table = $this->getTable('core/config_data');
        $data  = array(
            'scope'     => $scope,
            'scope_id'  => $scopeId,
            'path'      => $path,
            'value'     => $value
        );
        $this->getCoreConnection()->insertOnDuplicate($table, $data, array('value'));
        return $this;
    }

    /**
     * Delete config field values
     *
     * @param string $path
     * @param string $scope (default|stores|websites|config)
     * @return Mage_Core_Model_Resource_Setup
     */
    public function deleteConfigData($path, $scope = null)
    {
        $where = array('path = ?' => $path);
        if (!is_null($scope)) {
            $where['scope = ?'] = $scope;
        }
        $this->getCoreConnection()->delete($this->getTable('core/config_data'), $where);
        return $this;
    }

}
