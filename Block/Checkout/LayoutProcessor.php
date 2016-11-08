<?php

namespace Experius\AddressLines\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface{

    protected $scopeConfig;

    protected $logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function process($result){

        if($this->scopeConfig->getValue('customer/experius_address_lines/enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) &&
            isset($result['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']
                ['shipping-address-fieldset'])
        ){

            $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

            if(isset($shippingFields['street']['label'])){
                unset($shippingFields['street']['label']);
                unset($shippingFields['street']['required']);
            }

            if(isset($shippingFields['street'])){
                unset($shippingFields['street']['children'][1]['validation']);
                unset($shippingFields['street']['children'][2]['validation']);
            }

            if(isset($shippingFields['street']['label'])) {
                $shippingFields['street']['config']['additionalClasses'] = 'street experius-address-lines';
            }

            if(isset($shippingFields['street']['children'][0])){
                $shippingFields['street']['children'][0]['label'] = __('Street');
                $shippingFields['street']['children'][0]['required'] = True;
            }

            if(isset($shippingFields['street']['children'][1])){
                $shippingFields['street']['children'][1]['label'] = __('Housenumber');
                $shippingFields['street']['children'][1]['additionalClasses'] = 'experius-address-lines-housenumber';
                $shippingFields['street']['children'][1]['validation'] = ['required-entry'=>1,'validate-number'=>1];
                $shippingFields['street']['children'][1]['required'] = True;
            }

            if(isset($shippingFields['street']['children'][2])){
                $shippingFields['street']['children'][2]['label'] = __('Addition');
                $shippingFields['street']['children'][2]['additionalClasses'] = 'experius-address-lines-addition';
                $shippingFields['street']['children'][2]['required'] = True;
            }

            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;

        }

        return $result;
    }

}