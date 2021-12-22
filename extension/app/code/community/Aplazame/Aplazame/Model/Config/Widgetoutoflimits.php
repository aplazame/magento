<?php

class Aplazame_Aplazame_Model_Config_Widgetoutoflimits
{
    public function toOptionArray()
    {
        $helper = Mage::helper('aplazame');

        return array(
            array(
                'value' => 'show',
                'label' => $helper->__('Show'),
            ),
            array(
                'value' => 'hide',
                'label' => $helper->__('Hide'),
            ),
        );
    }
}
