<?php

class Aoe_Layout_Block_Widget_Container extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Aoe/Layout/widget/container.phtml');
    }

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
}
