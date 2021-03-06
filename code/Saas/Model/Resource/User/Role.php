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
 * Admin role resource model
 *
 * @category    Cm
 * @package     Cm_Saas
 */
class Cm_Saas_Model_Resource_User_Role extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('saas/user_role', 'role_id');
    }

    /**
     * Process role before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Cm_Saas_Model_Resource_User_Role
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ( !$object->getId() ) {
            $object->setCreated($this->formatDate(true));
        }
        $object->setModified($this->formatDate(true));
        return $this;
    }
}
