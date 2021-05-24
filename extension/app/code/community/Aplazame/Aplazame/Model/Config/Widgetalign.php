<?php

class Aplazame_Aplazame_Model_Config_Widgetalign
{
    public function toOptionArray()
    {
        $helper = Mage::helper('aplazame');

        return array(
            array(
                'value' => 'left',
                'label' => $helper->__('Left'),
            ),
            array(
                'value' => 'center',
                'label' => $helper->__('Center'),
            ),
            array(
                'value' => 'right',
                'label' => $helper->__('Right'),
            ),
        );
    }
}
