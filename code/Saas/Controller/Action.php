<?php
/**
 * LICENSE_HERE_OSL
 */

/**
 * Base saas controller
 */
class Cm_Saas_Controller_Action extends Mage_Core_Controller_Varien_Action
{

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'saas';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array();

    /**
     * Used module name in current controller
     */
    protected $_usedModuleName = 'saas';

    /**
     * Currently used area
     *
     * @var string
     */
    protected $_currentArea = 'saas';

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @return Cm_Saas_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('saas/session');
    }

    /**
     * @return Cm_Saas_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('saas');
    }

    /**
     * Define active menu item in menu block
     *
     * @param string $menuPath
     * @return Cm_Saas_Controller_Action
     */
    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        return $this;
    }

    /**
     * @param $label
     * @param $title
     * @param null $link
     * @return Cm_Saas_Controller_Action
     */
    protected function _addBreadcrumb($label, $title, $link=null)
    {
        $this->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
        return $this;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return Cm_Saas_Controller_Action
     */
    protected function _addContent(Mage_Core_Block_Abstract $block)
    {
        $this->getLayout()->getBlock('content')->append($block);
        return $this;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return $this
     */
    protected function _addLeft(Mage_Core_Block_Abstract $block)
    {
        $this->getLayout()->getBlock('left')->append($block);
        return $this;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return $this
     */
    protected function _addJs(Mage_Core_Block_Abstract $block)
    {
        $this->getLayout()->getBlock('js')->append($block);
        return $this;
    }

    /**
     * @return Cm_Saas_Controller_Action
     */
    public function preDispatch()
    {
        // override saas store design settings via stores section
        Mage::getDesign()
            ->setArea($this->_currentArea)
            ->setPackageName((string)Mage::getConfig()->getNode('stores/saas/design/package/name'))
            ->setTheme((string)Mage::getConfig()->getNode('stores/saas/design/theme/default'))
        ;
        foreach (array('layout', 'template', 'skin', 'locale') as $type) {
            if ($value = (string)Mage::getConfig()->getNode("stores/saas/design/theme/{$type}")) {
                Mage::getDesign()->setTheme($type, $value);
            }
        }

        $this->getLayout()->setArea($this->_currentArea);

        Mage::dispatchEvent('saas_controller_action_predispatch_start', array());
        parent::preDispatch();
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if (Mage::getSingleton('saas/session')->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_validateFormKey();
                $_keyErrorMsg = Mage::helper('saas')->__('Invalid Form Key. Please refresh the page.');
            } elseif (Mage::getSingleton('saas/url')->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = Mage::helper('saas')->__('Invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'error' => true,
                    'message' => $_keyErrorMsg
                )));
            } else {
                $this->_redirect( Mage::getSingleton('saas/session')->getUser()->getStartupPageUrl() );
            }
            return $this;
        }

        if ($this->getRequest()->isDispatched()
            && $this->getRequest()->getActionName() !== 'denied'
            && !$this->_isAllowed()) {
            $this->_forward('denied');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this;
        }

        if (is_null(Mage::getSingleton('saas/session')->getLocale())) {
            Mage::getSingleton('saas/session')->setLocale(Mage::app()->getLocale()->getLocaleCode());
        }

        return $this;
    }


    /**
     * Display Access Denied page or Login page
     */
    public function deniedAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
        if (!Mage::getSingleton('saas/session')->isLoggedIn()) {
            $this->_redirect('*/index/login');
            return;
        }
        $this->loadLayout(array('default', 'saas_denied'));
        $this->renderLayout();
    }

    /**
     * @param null $ids
     * @param bool $generateBlocks
     * @param bool $generateXml
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        parent::loadLayout($ids, $generateBlocks, $generateXml);
        $this->_initLayoutMessages('saas/session');
        return $this;
    }

    /**
     * @param null $coreRoute
     */
    public function norouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');
        $this->loadLayout(array('default', 'saas_noroute'));
        $this->renderLayout();
    }

    /**
     * Retrieve currently used module name
     *
     * @return string
     */
    public function getUsedModuleName()
    {
        return $this->_usedModuleName;
    }

    /**
     * Set currently used module name
     *
     * @param string $moduleName
     * @return Cm_Saas_Controller_Action
     */
    public function setUsedModuleName($moduleName)
    {
        $this->_usedModuleName = $moduleName;
        return $this;
    }

    /**
     * Translate a phrase
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->getUsedModuleName());
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }

    /**
     * Set referer url for redirect in response
     *
     * Is overridden here to set defaultUrl to saas base url
     *
     * @param   string $defaultUrl
     * @return  Cm_Saas_Controller_Action
     */
    protected function _redirectReferer($defaultUrl=null)
    {
        $defaultUrl = empty($defaultUrl) ? $this->getUrl('*') : $defaultUrl;
        parent::_redirectReferer($defaultUrl);
        return $this;
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return $this|\Mage_Core_Controller_Varien_Action
     */
    protected function _redirect($path, $arguments=array())
    {
        $this->getResponse()->setRedirect($this->getUrl($path, $arguments));
        return $this;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route='', $params=array())
    {
        return $this->_getHelper()->getUrl($route, $params);
    }

    /**
     * Validate Secret Key
     *
     * @return bool
     */
    protected function _validateSecretKey()
    {
        if (is_array($this->_publicActions) && in_array($this->getRequest()->getActionName(), $this->_publicActions)) {
            return true;
        }

        if (!($secretKey = $this->getRequest()->getParam(Cm_Saas_Model_Url::SECRET_KEY_PARAM_NAME, null))
            || $secretKey != Mage::getSingleton('saas/url')->getSecretKey()) {
            return false;
        }
        return true;
    }
}
