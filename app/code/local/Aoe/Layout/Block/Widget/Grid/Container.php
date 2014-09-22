<?php

class Aoe_Layout_Block_Widget_Grid_Container extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Set header text
     *
     * @param $text
     *
     * @return $this
     */
    public function setHeaderText($text)
    {
        $this->_headerText = $text;

        return $this;
    }

    /**
     * Skip parent method and call grandparent
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        return Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
    }
}
