<?php
/**
 * LICENSE_HERE_OSL
 */

/**
 * Saas general purpose session namespace
 */
class Cm_Saas_Model_Session extends Mage_Core_Model_Session_Abstract
{

    public function __construct()
    {
        $this->init('saas');
    }

}
