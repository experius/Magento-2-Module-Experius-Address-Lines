<?php

namespace Experius\AddressLines\Helper;

use Magento\Framework\DataObject;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    public function getModuleConfig($field = false, $group = false, $section = false)
    {
        $section = ($section) ? $section : 'customer';
        $group = ($group) ? $group : 'experius_address_lines';
        $field = ($field) ? $field : 'enabled';
        return $this->_scopeConfig->getValue($section.'/'.$group.'/'.$field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getValidationClassesAsArrayForLine($lineNumber){

        $validationClassesString = $this->getModuleConfig("line{$lineNumber}_validation_classes");

        $validationParts = explode(',',$validationClassesString);

        $validationClassesArray = [];

        foreach($validationParts as $validationPart){
            $validationPartArray = explode(':',$validationPart);
            $validationClassesArray[$validationPartArray[0]] = (int) $validationPartArray[1];
        }

        if($this->getModuleConfig("line{$lineNumber}_required")){
            $validationClassesArray['required-entry'] = 1;
        }

        return $validationClassesArray;
    }

}
