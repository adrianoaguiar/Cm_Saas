<?php
/**
 * LICENSE_HERE_OSL
 */


class Cm_Saas_Controller_Varien_Router_Saas extends Mage_Core_Controller_Varien_Router_Standard
{

    /**
     * Set default path
     */
    public function fetchDefault()
    {
        // set defaults
        $d = explode('/', $this->_getDefaultPath());
        $this->getFront()->setDefault(array(
            'module'     => !empty($d[0]) ? $d[0] : '',
            'controller' => !empty($d[1]) ? $d[1] : 'index',
            'action'     => !empty($d[2]) ? $d[2] : 'index'
        ));
    }

    /**
     * Get router default request path
     *
     * @return string
     */
    protected function _getDefaultPath()
    {
        return (string)Mage::getConfig()->getNode('default/saas/web/default');
    }

    /**
     * Return false to skip this router
     *
     * @return bool
     */
    protected function _beforeModuleMatch()
    {
        return true;
    }

    /**
     * Router was matched but return false to abort
     *
     * @return bool
     */
    protected function _afterModuleMatch()
    {
        return true;
    }

    /**
     * Return true to go to noRoute page if router matched but no controller was found
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    /**
     * Check whether URL for corresponding path should use https protocol
     *
     * @param string $path
     * @return bool
     */
    protected function _shouldBeSecure($path)
    {
        return substr((string)Mage::getConfig()->getNode('default/web/unsecure/base_url'), 0, 5) === 'https'
            || Mage::getStoreConfigFlag(Cm_Saas_Model_Url::XML_PATH_SECURE_IN_SAAS, Mage_Core_Model_App::ADMIN_STORE_ID)
                && substr((string)Mage::getConfig()->getNode('default/web/secure/base_url'), 0, 5) === 'https';
    }

    /**
     * Retrieve current secure url
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        return parent::_getCurrentSecureUrl($request);
    }

    /**
     * Emulate custom saas url
     *
     * @param string $configArea
     * @param bool $useRouterName
     */
    public function collectRoutes($configArea, $useRouterName)
    {
        if ((string)Mage::getConfig()->getNode(Cm_Saas_Helper_Data::XML_PATH_CUSTOM_PATH)) {
            $customUrl = (string)Mage::getConfig()->getNode(Cm_Saas_Helper_Data::XML_PATH_CUSTOM_PATH);
            $xmlPath = Cm_Saas_Helper_Data::XML_PATH_SAAS_ROUTER_FRONTNAME;
            if ((string)Mage::getConfig()->getNode($xmlPath) != $customUrl) {
                Mage::getConfig()->setNode($xmlPath, $customUrl, true);
            }
        }
        parent::collectRoutes($configArea, $useRouterName);
    }

}
