<?php

class Aoe_Layout_Test_Model_Layout extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function getModel()
    {
        /** @var Aoe_Layout_Model_Layout $layout */
        $layout = Mage::getModel('core/layout');
        $this->assertInstanceOf('Aoe_Layout_Model_Layout', $layout);
    }
}
