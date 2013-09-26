<?php
/**
 * LICENSE_HERE_OSL
 */

/**
 * Saas base helper
 */
class Cm_Saas_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_SAAS_ROUTER_FRONTNAME  = 'saas/routers/saas/args/frontName';
    const XML_PATH_USE_CUSTOM_PATH        = 'default/saas/url/use_custom_path';
    const XML_PATH_CUSTOM_PATH            = 'default/saas/url/custom_path';
    const XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'default/saas/emails/password_reset_link_expiration_period';

    protected $_website = NULL;
    protected $_accounts = [];
    protected $_dbs = [];

    /**
     * @return bool
     */
    public function isSaas()
    {
        return !! $this->getWebsite() && $this->getWebsite()->getAccountId();
    }

    /**
     * @return bool
     */
    public function useDbPerWebsite()
    {
        static $dbPerWebsite = NULL;
        if ($dbPerWebsite === NULL) {
            $dbPerWebsite = Mage::app()->getConfig()->getNode('global/saas')->is('db_per_website');
        }
        return $dbPerWebsite;
    }

    /**
     * @param Mage_Core_Model_Website|string|int|bool|null $website
     */
    public function setWebsite($website)
    {
        if ($website === FALSE) {
            $this->_website = NULL;
        } else {
            $website = Mage::app()->getWebsite($website);
            if ($website->getAccountId()) {
                $this->_website = $website;
            }
        }
    }

    /**
     * @return Mage_Core_Model_Website|null
     */
    public function getWebsite()
    {
        return $this->_website;
    }

    /**
     * @param int|null $accountId
     * @return Cm_Saas_Model_Account|null
     * @throws Exception
     */
    public function getAccount($accountId = NULL)
    {
        if ( ! $accountId) {
            $accountId = $this->getCurrentAccountId();
        }
        if ( ! $accountId) {
            return NULL;
        }
        if ( ! isset($this->_accounts[$accountId])) {
            $account = Mage::getModel('saas/account')->load($accountId);
            if ( ! $account->getId()) {
                throw new Exception('Could not load account.');
            }
            $this->_accounts[$accountId] = $account;
        }
        return $this->_accounts[$accountId];
    }

    /**
     * @return int|bool
     */
    public function getCurrentAccountId()
    {
        return $this->getWebsite() ? ($this->getWebsite()->getAccountId() ?: FALSE) : FALSE;
    }

    /**
     * @throws Exception
     * @return Cm_Saas_Model_Db
     */
    public function getCurrentDb()
    {
        $dbId = $this->getWebsite()->getDbId();
        if ( ! $dbId) {
            throw new Exception('Current website does not have db_id.');
        }
        if ( ! isset($this->_dbs[$dbId])) {
            $db = Mage::getModel('saas/db')->load($dbId);
            if ( ! $db->getId()) {
                throw new Exception('Could not load website database.');
            }
            $this->_dbs[$dbId] = $db;
        }
        return $this->_dbs[$dbId];
    }

    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     */
    public function generateResetPasswordLinkToken()
    {
        return Mage::helper('core')->uniqHash();
    }

    /**
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int) Mage::getConfig()->getNode(self::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD);
    }
}
