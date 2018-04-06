<?php
namespace Flownative\BestBuyApi\Domain\Model;

/**
 *
 */
class Product
{
    /**
     * @var string
     */
    protected $sku;

    /**
     * @var string
     */
    protected $productId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isNew;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var
     */
    protected $regularPrice;

    /**
     * @var array
     */
    protected $relatedProducts;

    /**
     * @var string
     */
    protected $shortDescription;

    /**
     * @var string
     */
    protected $manufacturer;

    /**
     * @var string
     */
    protected $image;
}
