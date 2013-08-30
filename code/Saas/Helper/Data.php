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

}
