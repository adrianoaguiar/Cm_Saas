<?php


class Cm_Saas_Model_Resource_Resource extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Database versions
     *
     * @var array
     */
    protected $_versions        = null;

    /**
     * Resource data versions cache array
     *
     * @var array
     */
    protected $_dataVersions    = null;

    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('saas/resource', 'store_id');
    }

    /**
     * Fill static versions arrays.
     * This routine fetches Db and Data versions of at once to optimize sql requests. However, when upgrading, it's
     * possible that 'data' column will be created only after all Db installs are passed. So $neededType contains
     * information on main purpose of calling this routine, and even when 'data' column is absent - it won't require
     * reissuing new sql just to get 'db' version of module.
     *
     * @param string $needType Can be 'db' or 'data'
     * @return Mage_Core_Model_Resource_Resource
     */
    protected function _loadVersionData($needType)
    {
        if ((($needType == 'db') && is_null($this->_versions))
            || (($needType == 'data') && is_null($this->_dataVersions))) {
            $this->_versions     = array(); // Db version column always exists
            $this->_dataVersions = null; // Data version array will be filled only if Data column exist

            if ($this->_getReadAdapter()->isTableExists($this->getMainTable())) {
                $select = $this->_getReadAdapter()->select()
                    ->from($this->getMainTable(), '*');
                $rowset = $this->_getReadAdapter()->fetchAll($select);
                foreach ($rowset as $row) {
                    $this->_versions[$row['code']] = $row['version'];
                    if (array_key_exists('data_version', $row)) {
                        if (is_null($this->_dataVersions)) {
                            $this->_dataVersions = array();
                        }
                        $this->_dataVersions[$row['code']] = $row['data_version'];
                    }
                }
            }
        }

        return $this;
    }


    /**
     * Get Module version from DB
     *
     * @param string $resName
     * @return bool|string
     */
    public function getDbVersion($resName)
    {
        if (!$this->_getReadAdapter()) {
            return false;
        }
        $this->_loadVersionData('db');
        return isset($this->_versions[$resName]) ? $this->_versions[$resName] : false;
    }

    /**
     * Set module version into DB
     *
     * @param string $resName
     * @param string $version
     * @return int
     */
    public function setDbVersion($resName, $version)
    {
        $dbModuleInfo = array(
            'code'    => $resName,
            'version' => $version,
        );

        if ($this->getDbVersion($resName)) {
            $this->_versions[$resName] = $version;
            return $this->_getWriteAdapter()->update($this->getMainTable(),
                    $dbModuleInfo,
                    array('code = ?' => $resName));
        } else {
            $this->_versions[$resName] = $version;
            return $this->_getWriteAdapter()->insert($this->getMainTable(), $dbModuleInfo);
        }
    }

    /**
     * Get resource data version
     *
     * @param string $resName
     * @return string|false
     */
    public function getDataVersion($resName)
    {
        if (!$this->_getReadAdapter()) {
            return false;
        }

        $this->_loadVersionData('data');

        return isset($this->_dataVersions[$resName]) ? $this->_dataVersions[$resName] : false;
    }

    /**
     * Specify resource data version
     *
     * @param string $resName
     * @param string $version
     * @return Mage_Core_Model_Resource_Resource
     */
    public function setDataVersion($resName, $version)
    {
        $data = array(
            'code'          => $resName,
            'data_version'  => $version
        );

        if ($this->getDbVersion($resName) || $this->getDataVersion($resName)) {
            $this->_dataVersions[$resName] = $version;
            $this->_getWriteAdapter()->update($this->getMainTable(), $data, array('code = ?' => $resName));
        } else {
            $this->_dataVersions[$resName] = $version;
            $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        }
        return $this;
    }
}
