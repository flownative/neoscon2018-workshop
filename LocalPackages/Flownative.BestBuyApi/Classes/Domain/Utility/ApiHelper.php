<?php
namespace Flownative\BestBuyApi\Domain\Utility;

/**
 *
 */
abstract class ApiHelper
{
    /**
     * @param string $apiKey
     * @return array
     */
    public static function getDefaultApiParameters(string $apiKey)
    {
        return [
            'apiKey' => $apiKey,
            'format' => 'json',
            'pageSize' => 50
        ];
    }
}
