<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @since  2014-11-17
 */
class Aoe_Layout_Block_Widget_Tab_Form extends Aoe_Layout_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->hasData('tab_label') ? $this->getData('tab_label') : $this->getData('label');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->hasData('tab_title') ? $this->getData('tab_title') : $this->getData('title');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return ($this->hasData('can_show_tab') ? (bool)$this->getData('can_show_tab') : true);
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return ($this->hasData('is_hidden') ? (bool)$this->getData('is_hidden') : false);
    }
}
