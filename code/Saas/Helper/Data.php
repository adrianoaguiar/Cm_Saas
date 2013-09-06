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

    /**
     * @return bool
     */
    public function isSaas()
    {
        return !! Mage::app()->getWebsite()->getAccountId();
    }

    /**
     * @return int|bool
     */
    public function getCurrentAccountId()
    {
        return Mage::app()->getWebsite()->getAccountId() ?: FALSE;
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
