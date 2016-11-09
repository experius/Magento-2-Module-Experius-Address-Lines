<?php

namespace Experius\AddressLines\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface{

    protected $scopeConfig;

    protected $logger;

    protected $addressLineHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Experius\AddressLines\Helper\Data $addressLineHelper
    ){
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->addressLineHelper = $addressLineHelper;
    }

    public function process($result){

        if(!$this->addressLineHelper->getModuleConfig('enabled')) {
            return $result;
        }

        if(isset($result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset'])) {

            $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

            $shippingFields = $this->modifyStreetUiComponents($shippingFields);

            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;

        }

        $result = $this->getBillingFormFields($result);

        return $result;
    }


    public function getBillingFormFields($result){

        if(isset($result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list'])) {

            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {

                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $billingFields = $this->modifyStreetUiComponents($billingFields);

                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'] = $billingFields;

            }
        }

        return $result;

    }

    public function modifyStreetUiComponents($addressResult)
    {
        if(isset($addressResult['street']['label'])){
            unset($addressResult['street']['label']);
            unset($addressResult['street']['required']);
        }

        if(isset($addressResult['street'])){
            unset($addressResult['street']['children'][1]['validation']);
            unset($addressResult['street']['children'][2]['validation']);
        }

        if(isset($addressResult['street']['config']['additionalClasses'])) {
            $addressResult['street']['config']['additionalClasses'] = 'street experius-address-lines';
        }

        $lineCount = 0;

        while($lineCount<4){

            $lineNumber = $lineCount+1;

            if(isset($addressResult['street']['children'][$lineCount])){
                $addressResult['street']['children'][$lineCount]['label'] = ($this->addressLineHelper->getModuleConfig("line{$lineNumber}_label")) ? $this->addressLineHelper->getModuleConfig("line{$lineNumber}_label") : __('Address Line');
                $addressResult['street']['children'][$lineCount]['additionalClasses'] = 'experius-address-line-one';
                $addressResult['street']['children'][$lineCount]['validation'] = $this->addressLineHelper->getValidationClassesAsArrayForLine($lineNumber);
                $addressResult['street']['children'][$lineCount]['required'] = ($this->addressLineHelper->getModuleConfig("line{$lineNumber}_required")) ? True : False;
            }

            $lineCount++;
        }

        return $addressResult;
    }


}