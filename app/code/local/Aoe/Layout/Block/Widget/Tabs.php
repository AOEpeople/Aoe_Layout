<?php
/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @since  2014-11-10
 */
class Aoe_Layout_Block_Widget_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Add new tab
     *
     * @param   string $tabId
     * @param   array|Varien_Object $tab
     * @return  Mage_Adminhtml_Block_Widget_Tabs
     */
    public function addTab($tabId, $tab)
    {
        parent::addTab($tabId, $tab);

        if($this->_tabs[$tabId] instanceof Mage_Core_Block_Abstract && $this->_tabs[$tabId]->getParentBlock() === null) {
           $this->append($this->_tabs[$tabId]);
        }

        return $this;
    }
}
