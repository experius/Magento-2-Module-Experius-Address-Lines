<?php
namespace Experius\AddressLines\Helper;
use Magento\Framework\DataObject;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
    public function getModuleConfig($field = false, $group = false, $section = false)
    {
        $section = ($section) ? $section : 'customer';
        $group = ($group) ?  $group : 'experius_address_lines';
        $field = ($field) ? $field : 'enabled';
        //var_dump("{$section}/{$group}/{$field}"); exit();die();
        return $this->scopeConfig->getValue("{$section}/{$group}/{$field}", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isLineEnabled($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        return $this->getModuleConfig("line_enabled", $group);
    }

    public function getLineLabel($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        $label = ($this->getModuleConfig("line_label", $group))
            ? $this->getModuleConfig("line_label", $group)
            : __('Address Line');
        return $label;
    }

    public function getValidationMaxLength($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        $maxLength = ($this->getModuleConfig("line_max_length", $group))
            ? $this->getModuleConfig("line_max_length", $group)
            : false;
        return $maxLength;
    }

    public function getValidationMinLength($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        $maxLength = ($this->getModuleConfig("line_min_length", $group))
            ? $this->getModuleConfig("line_min_length", $group)
            : false;
        return $maxLength;
    }

    public function isLineRequired($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        return $this->getModuleConfig("line_required", $group);
    }

    public function getValidationClassesForLine($lineNumber)
    {
        if (!$this->getValidationClassesAsArrayForLine($lineNumber)) {
            return;
        }
        $validationArray = $this->getValidationClassesAsArrayForLine($lineNumber);
        if (key_exists('validate-number',$validationArray)) {
            $validationArray['validate-digits'] = 1;
            unset($validationArray['validate-number']);
        }
        $validationClassesString = ' ' . implode(' ', array_keys($validationArray));
        return $validationClassesString;
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
            if (!empty($validationPartArray)) {
                $validationClassesArray[$validationPartArray[0]] = (int) $validationPartArray[1];
            }
        }
        if($this->isLineRequired($lineNumber)){
            $validationClassesArray['required-entry'] = 1;
        }
        return $validationClassesArray;
    }
}
