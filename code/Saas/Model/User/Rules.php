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
 * Admin Rules Model
 *
 * @method Cm_Saas_Model_Resource_Rules _getResource()
 * @method Cm_Saas_Model_Resource_Rules getResource()
 * @method int getRoleId()
 * @method Cm_Saas_Model_User_Rules setRoleId(int $value)
 * @method string getResourceId()
 * @method Cm_Saas_Model_User_Rules setResourceId(string $value)
 * @method string getPrivileges()
 * @method Cm_Saas_Model_User_Rules setPrivileges(string $value)
 * @method int getAssertId()
 * @method Cm_Saas_Model_User_Rules setAssertId(int $value)
 * @method string getRoleType()
 * @method Cm_Saas_Model_User_Rules setRoleType(string $value)
 * @method string getPermission()
 * @method Cm_Saas_Model_User_Rules setPermission(string $value)
 *
 * @category    Cm
 * @package     Cm_Saas
 */
class Cm_Saas_Model_User_Rules extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('saas/user_rules');
    }

    public function update() {
        $this->getResource()->update($this);
        return $this;
    }

    public function getCollection() {
        return Mage::getResourceModel('saas/user_permissions_collection');
    }

    public function saveRel() {
        $this->getResource()->saveRel($this);
        return $this;
    }
}
