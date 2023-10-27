<?php
/**
 * Copyright © Happy Horizon Utrecht Development & Technology B.V. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Experius\AddressLines\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @param $field
     * @param $group
     * @param $section
     * @return mixed|string
     */
    public function getModuleConfig($field = null, $group = null, $section = null)
    {
        $section = $section ?: 'customer';
        $group = $group ?: 'experius_address_lines';
        $field = $field ?: 'enabled';
        return $this->scopeConfig->getValue("{$section}/{$group}/{$field}", ScopeInterface::SCOPE_STORE) ?? '';
    }

    /**
     * @param $field
     * @param $group
     * @param $section
     * @return bool
     */
    public function isSetFlag($field = null, $group = null, $section = null): bool
    {
        $section = $section ?: 'customer';
        $group = $group ?: 'experius_address_lines';
        $field = $field ?: 'enabled';
        return $this->scopeConfig->isSetFlag("{$section}/{$group}/{$field}", ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $lineNumber
     * @return bool
     */
    public function isLineEnabled($lineNumber): bool
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        return $this->isSetFlag('line_enabled', $group);
    }

    /**
     * @param $lineNumber
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function getLineLabel($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        $label = ($this->getModuleConfig('line_label', $group))
            ? $this->getModuleConfig('line_label', $group)
            : __('Address Line');
        return $label;
    }

    /**
     * @param $lineNumber
     * @return false|mixed|string
     */
    public function getValidationMaxLength($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        $maxLength = ($this->getModuleConfig('line_max_length', $group))
            ? $this->getModuleConfig('line_max_length', $group)
            : false;
        return $maxLength;
    }

    /**
     * @param $lineNumber
     * @return false|mixed|string
     */
    public function getValidationMinLength($lineNumber)
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        $maxLength = ($this->getModuleConfig('line_min_length', $group))
            ? $this->getModuleConfig('line_min_length', $group)
            : false;
        return $maxLength;
    }

    /**
     * @param $lineNumber
     * @return bool
     */
    public function isLineRequired($lineNumber): bool
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";
        return $this->isSetFlag('line_required', $group);
    }

    /**
     * @param $lineNumber
     * @return string
     */
    public function getValidationClassesForLine($lineNumber): string
    {
        if (!$this->getValidationClassesAsArrayForLine($lineNumber)) {
            return '';
        }
        $validationArray = $this->getValidationClassesAsArrayForLine($lineNumber);
        if (key_exists('validate-number', $validationArray)) {
            $validationArray['validate-digits'] = 1;
            unset($validationArray['validate-number']);
        }
        return ' ' . implode(' ', array_keys($validationArray));
    }

    /**
     * @param $lineNumber
     * @return array
     */
    public function getValidationClassesAsArrayForLine($lineNumber): array
    {
        $group = "experius_address_lines/experius_address_line{$lineNumber}";

        $validationClassesString = $this->getModuleConfig('line_validation_classes', $group);
        $validationParts = explode(',', $validationClassesString);
        $validationClassesArray = [];
        foreach ($validationParts as $validationPart) {
            if (!$validationPart) {
                continue;
            }
            $validationPartArray = explode(':', $validationPart);
            if (!empty($validationPartArray)) {
                $validationClassesArray[$validationPartArray[0]] = (int)$validationPartArray[1];
            }
        }
        if ($this->isLineRequired($lineNumber)) {
            $validationClassesArray['required-entry'] = 1;
        }
        return $validationClassesArray;
    }
}
