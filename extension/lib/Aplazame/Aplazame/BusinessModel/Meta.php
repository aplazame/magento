<?php

/**
 * Meta.
 */
class Aplazame_Aplazame_BusinessModel_Meta
{
    public static function create()
    {
        $moduleConfig = Mage::getConfig()->getModuleConfig('Aplazame_Aplazame');

        $aMeta = new self();
        $aMeta->module = array(
            'name' => 'aplazame:magento',
            'version' => (string) $moduleConfig->version[0],
        );
        $aMeta->version = Mage::getVersion();

        return $aMeta;
    }
}
