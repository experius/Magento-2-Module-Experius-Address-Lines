<?php
namespace Experius\AddressLines\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $resource;

    protected $eavAttribute;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resourceConnection,
        Attribute $eavAttribute
    ) {
        $this->resource = $resourceConnection;
        $this->eavAttribute = $eavAttribute;
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
            if (!$validationPart) {
                continue;
            }
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

    /**
     * Return form attribute IDs by form code
     *
     * @param string $formCode
     * @return array
     */
    public function isUsedInForm($attributeCode, $formCode = 'customer_register_address', $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS)
    {
        $attributeId = $this->eavAttribute->getIdByCode( $entityType, $attributeCode);
        $bind = ['attribute_id' => $attributeId, 'form_code' => $formCode];
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName('customer_form_attribute'),
            'attribute_id'
        )->where(
             'attribute_id = :attribute_id AND form_code = :form_code'
        );
        return $this->resource->getConnection()->fetchRow($select, $bind);
    }

}
