<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Cm
 * @package     Cm_Saas
 * TODO_COPYRIGHT
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Resources and connections registry and factory
 */
class Cm_Saas_Model_Resource extends Mage_Core_Model_Resource
{

    /**
     * Creates a connection to resource whenever needed
     *
     * @param string $name
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection($name)
    {
        $isDbPerWebsite = $db = FALSE;
        if (substr($name,0,5) == 'saas_' && Mage::helper('saas')->useDbPerWebsite() && Mage::helper('saas')->getWebsite())
        {
            $isDbPerWebsite = TRUE;
            $db = Mage::helper('saas')->getCurrentDb();
            $name = $db->getName().'_'.Mage::helper('saas')->getWebsite()->getCode().'_saas';
        }

        if (isset($this->_connections[$name])) {
            $connection = $this->_connections[$name];
            if (isset($this->_skippedConnections[$name]) && !Mage::app()->getIsCacheLocked()) {
                $connection->setCacheAdapter(Mage::app()->getCache());
                unset($this->_skippedConnections[$name]);
            }

            return $connection;
        }
        if ($isDbPerWebsite) {
            $connConfig = $db->getConnectionConfig(Mage::helper('saas')->getWebsite()->getCode());
        } else {
            $connConfig = Mage::getConfig()->getResourceConnectionConfig($name);
        }

        if (!$connConfig) {
            $this->_connections[$name] = $this->_getDefaultConnection($name);
            return $this->_connections[$name];
        }
        if (!$connConfig->is('active', 1)) {
            return false;
        }

        if ( ! $isDbPerWebsite) {
            $origName = $connConfig->getParent()->getName();
            if (isset($this->_connections[$origName])) {
                $this->_connections[$name] = $this->_connections[$origName];
                return $this->_connections[$origName];
            }
        }

        $connection = $this->_newConnection((string)$connConfig->type, $connConfig);
        if ($connection) {
            if (Mage::app()->getIsCacheLocked()) {
                $this->_skippedConnections[$name] = true;
            } else if (method_exists($connection, 'setCacheAdapter')) {
                $connection->setCacheAdapter(Mage::app()->getCache());
            }
        }

        $this->_connections[$name] = $connection;
        if ( ! $isDbPerWebsite && $origName !== $name) {
            $this->_connections[$origName] = $connection;
        }

        return $connection;
    }

}
