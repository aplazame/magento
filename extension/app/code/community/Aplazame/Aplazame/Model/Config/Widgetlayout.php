<?php

class Aplazame_Aplazame_Model_Config_Widgetlayout
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'horizontal',
                'label' => 'Horizontal',
            ),
            array(
                'value' => 'vertical',
                'label' => 'Vertical',
            ),
        );
    }
}
