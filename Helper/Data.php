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
        $group = ($group) ?  $group : 'experius_address_lines';
        $field = ($field) ? $field : 'enabled';
        //var_dump("{$section}/{$group}/{$field}"); exit();die();
        return $this->_scopeConfig->getValue("{$section}/{$group}/{$field}", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getValidationClassesAsArrayForLine($lineNumber) 
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        
        $validationClassesString = $this->getModuleConfig("line_validation_classes", $group);
        //var_dump($validationClassesString);
        $validationParts = explode(',', $validationClassesString);
        //var_dump($validationParts); die();

        $validationClassesArray = [];

        foreach($validationParts as $validationPart) {
            
            $validationPartArray = explode(':', $validationPart);
            if (empty($validationPartArray)) {
                $validationClassesArray[$validationPartArray[0]] = (int) $validationPartArray[1];
            }
        }

        if($this->getModuleConfig("line_required", $group)){
            $validationClassesArray['required-entry'] = 1;
        }

        return $validationClassesArray;
    }

}
