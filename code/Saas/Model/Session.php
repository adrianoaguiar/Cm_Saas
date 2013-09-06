<?php
/**
 * LICENSE_HERE_OSL
 */

/**
 * Saas general purpose session namespace
 *
 * @method Cm_Saas_Model_Session setAcl(Cm_Saas_Model_User_Acl $value)
 * @method Cm_Saas_Model_User_Acl|null getAcl()
 * @method Cm_Saas_Model_Session setUser(Cm_Saas_Model_Url $value)
 * @method Cm_Saas_Model_User|null getUser()
 */
class Cm_Saas_Model_Session extends Mage_Core_Model_Session_Abstract
{

    /**
     * Whether it is the first page after successful login
     *
     * @var boolean
     */
    protected $_isFirstPageAfterLogin;

    
    public function __construct()
    {
        $this->init('saas');
    }

    /**
     * Pull out information from session whether there is currently the first page after log in
     *
     * The idea is to set this value on login(), then redirect happens,
     * after that on next request the value is grabbed once the session is initialized
     * Since the session is used as a singleton, the value will be in $_isFirstPageAfterLogin until the end of request,
     * unless it is reset intentionally from somewhere
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Mage_Admin_Model_Session
     * @see self::login()
     */
    public function init($namespace, $sessionName = null)
    {
        parent::init($namespace, $sessionName);
        $this->isFirstPageAfterLogin();
        return $this;
    }

    /**
     * Try to login user in admin
     *
     * @param  string $username
     * @param  string $password
     * @param  Mage_Core_Controller_Request_Http $request
     * @return Cm_Saas_Model_User|null
     */
    public function login($username, $password, $request = null)
    {
        if (empty($username) || empty($password)) {
            return NULL;
        }

        $user = Mage::getModel('saas/user'); /** @var $user Cm_Saas_Model_User */
        try {
            $user->login($username, $password);
            if ($user->getId()) {
                $this->renewSession();

                if (Mage::getSingleton('saas/url')->useSecretKey()) {
                    Mage::getSingleton('saas/url')->renewSecretUrls();
                }
                $this->setIsFirstPageAfterLogin(true);
                $this->setUser($user);
                $this->setAcl(Mage::getResourceModel('saas/user_acl')->loadAcl());

                $requestUri = $this->_getRequestUri($request);
                if ($requestUri) {
                    Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));
                    header('Location: ' . $requestUri);
                    exit;
                }
            } else {
                Mage::throwException(Mage::helper('saas')->__('Invalid User Name or Password.'));
            }
        } catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('admin_session_user_login_failed',
                array('user_name' => $username, 'exception' => $e));
            if ($request && !$request->getParam('messageSent')) {
                $this->addError($e->getMessage());
                $request->setParam('messageSent', true);
            }
        }

        return $user;
    }

    /**
     * Refresh ACL resources stored in session
     *
     * @param  Cm_Saas_Model_User $user
     * @return Mage_Admin_Model_Session
     */
    public function refreshAcl($user = null)
    {
        if (is_null($user)) {
            $user = $this->getUser();
        }
        if (!$user) {
            return $this;
        }
        if (!$this->getAcl() || $user->getReloadAclFlag()) {
            $this->setAcl(Mage::getResourceModel('saas/user_acl')->loadAcl());
        }
        if ($user->getReloadAclFlag()) {
            $user->unsetData('password');
            $user->setReloadAclFlag('0')->save();
        }
        return $this;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * Mage::getSingleton('saas/session')->isAllowed('saas/catalog')
     * Mage::getSingleton('saas/session')->isAllowed('catalog')
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        $user = $this->getUser();
        $acl = $this->getAcl();

        if ($user && $acl) {
            if (!preg_match('/^saas/', $resource)) {
                $resource = 'saas/' . $resource;
            }

            try {
                return $acl->isAllowed($user->getAclRole(), $resource, $privilege);
            } catch (Exception $e) {
                try {
                    if (!$acl->has($resource)) {
                        return $acl->isAllowed($user->getAclRole(), null, $privilege);
                    }
                } catch (Exception $e) { }
            }
        }
        return false;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getUser() && $this->getUser()->getId();
    }

    /**
     * Check if it is the first page after successfull login
     *
     * @return boolean
     */
    public function isFirstPageAfterLogin()
    {
        if (is_null($this->_isFirstPageAfterLogin)) {
            $this->_isFirstPageAfterLogin = $this->getData('is_first_visit', true);
        }
        return $this->_isFirstPageAfterLogin;
    }

    /**
     * Setter whether the current/next page should be treated as first page after login
     *
     * @param bool $value
     * @return Mage_Admin_Model_Session
     */
    public function setIsFirstPageAfterLogin($value)
    {
        $this->_isFirstPageAfterLogin = (bool)$value;
        return $this->setIsFirstVisit($this->_isFirstPageAfterLogin);
    }

    /**
     * Custom REQUEST_URI logic
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string|null
     */
    protected function _getRequestUri($request = null)
    {
        if (Mage::getSingleton('saas/url')->useSecretKey()) {
            return Mage::getSingleton('saas/url')->getUrl('*/*/*', array('_current' => true));
        } elseif ($request) {
            return $request->getRequestUri();
        } else {
            return null;
        }
    }

}
