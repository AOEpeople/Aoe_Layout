<?php
abstract class Aoe_Layout_Controller_Abstract extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return Mage_Admin_Model_Session
     */
    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }
}
