<?php
/**
 * Copyright © Happy Horizon Utrecht Development & Technology B.V. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Experius\AddressLines\Block\Checkout;

use Experius\AddressLines\Helper\Data;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param Data $addressLineHelper
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected LoggerInterface $logger,
        protected Data $addressLineHelper
    ) {
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if (!$this->addressLineHelper->getModuleConfig('enabled')) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['shipping-step']
            ['children']['shippingAddress']
            ['children']['shipping-address-fieldset'])
        ) {
            $shippingFields = $jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['shipping-step']
            ['children']['shippingAddress']
            ['children']['shipping-address-fieldset']
            ['children'];

            $shippingFields = $this->modifyStreetUiComponents($shippingFields);

            $jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['shipping-step']
            ['children']['shippingAddress']
            ['children']['shipping-address-fieldset']
            ['children'] = $shippingFields;

        }

        $jsLayout = $this->getBillingFormFields($jsLayout);

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return array
     */
    public function getBillingFormFields($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['billing-step']
            ['children']['payment']
            ['children']['payments-list'])
        ) {
            $paymentForms = $jsLayout['components']['checkout']
            ['children']['steps']
            ['children']['billing-step']
            ['children']['payment']
            ['children']['payments-list']
            ['children'];

            foreach (array_keys($paymentForms) as $paymentMethodForm) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($jsLayout['components']['checkout']
                    ['children']['steps']
                    ['children']['billing-step']
                    ['children']['payment']
                    ['children']['payments-list']
                    ['children'][$paymentMethodCode . '-form'])
                ) {
                    continue;
                }

                $billingFields = $jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['billing-step']
                ['children']['payment']
                ['children']['payments-list']
                ['children'][$paymentMethodCode . '-form']
                ['children']['form-fields']
                ['children'];

                $billingFields = $this->modifyStreetUiComponents($billingFields);

                $jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['billing-step']
                ['children']['payment']
                ['children']['payments-list']
                ['children'][$paymentMethodCode . '-form']
                ['children']['form-fields']
                ['children'] = $billingFields;
            }
        }

        return $jsLayout;
    }

    /**
     * @param $addressResult
     * @return array
     */
    public function modifyStreetUiComponents($addressResult)
    {
        if (isset($addressResult['street']['label'])) {
            unset($addressResult['street']['label']);
            unset($addressResult['street']['required']);
        }

        if (isset($addressResult['street'])) {
            unset($addressResult['street']['children'][1]['validation']);
            unset($addressResult['street']['children'][2]['validation']);
        }

        if (isset($addressResult['street']['config']['template'])) {
            $addressResult['street']['config']['template'] = 'Experius_AddressLines/group/group';
        }

        if (isset($addressResult['street']['config']['additionalClasses'])) {
            $addressResult['street']['config']['additionalClasses'] =
                $addressResult['street']['config']['additionalClasses'] . ' experius-address-lines';
        }

        $lineCount = 0;

        while ($lineCount < 4) {

            $lineNumber = $lineCount + 1;

            if (isset($addressResult['street']['children'][$lineCount])) {
                $label = $this->addressLineHelper->getLineLabel($lineNumber);

                if ($this->addressLineHelper->isLineEnabled($lineNumber)) {
                    $addressResult['street']['children'][$lineCount]['label'] = $label;
                    $addressResult['street']['children'][$lineCount]['additionalClasses'] = 'experius-address-line-one';
                    $addressResult['street']['children'][$lineCount]['validation'] =
                        $this->addressLineHelper->getValidationClassesAsArrayForLine($lineNumber);
                    $addressResult['street']['children'][$lineCount]['required'] =
                        (bool)$this->addressLineHelper->isLineRequired($lineNumber);
                }
            }

            $lineCount++;
        }

        return $addressResult;
    }
}
