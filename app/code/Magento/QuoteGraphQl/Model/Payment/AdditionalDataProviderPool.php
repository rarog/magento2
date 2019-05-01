<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

/**
 * Class AdditionalDataProviderPool
 *
 * @package Magento\QuoteGraphQl\Model\Cart\Payment
 */
class AdditionalDataProviderPool
{
    /**
     * @var AdditionalDataProviderInterface[]
     */
    private $dataProviders;

    /**
     * AdditionalDataProviderPool constructor.
     * @param array $dataProviders
     */
    public function __construct(array $dataProviders = [])
    {
        $this->dataProviders = $dataProviders;
    }

    /**
     * Returns additional data for the payment method
     *
     * @param string $methodCode
     * @param array $args
     * @return array
     */
    public function getData(string $methodCode, array $args): array
    {
        $additionalData = [];
        if (isset($this->dataProviders[$methodCode])) {
            $additionalData = $this->dataProviders[$methodCode]->getData($args);
        }

        return $additionalData;
    }
}