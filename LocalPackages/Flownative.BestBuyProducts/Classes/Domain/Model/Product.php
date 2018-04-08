<?php
namespace Flownative\BestBuyProducts\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Product
{
    /**
     * @var string
     * @Flow\Identity
     * @ORM\Id
     */
    protected $sku;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var int
     */
    protected $regularPrice;

    /**
     * @var array
     */
    protected $relatedProducts = [];

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $shortDescription = '';

    /**
     * @var string
     */
    protected $manufacturer = '';

    /**
     * @var string
     */
    protected $image = '';

    /**
     * @var string
     */
    protected $color = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $modelNumber = '';

    /**
     * @var Category
     * @ORM\ManyToOne
     * @ORM\Column(nullable=true)
     */
    protected $category;

    /**
     * Constructor
     *
     * @param string $sku
     */
    public function __construct(string $sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getRegularPrice(): int
    {
        return $this->regularPrice;
    }

    /**
     * @param int $regularPrice
     */
    public function setRegularPrice(int $regularPrice): void
    {
        $this->regularPrice = $regularPrice;
    }

    /**
     * @return array
     */
    public function getRelatedProducts(): array
    {
        return $this->relatedProducts;
    }

    /**
     * @param array $relatedProducts
     */
    public function setRelatedProducts(array $relatedProducts): void
    {
        $this->relatedProducts = $relatedProducts;
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     */
    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * @param string $manufacturer
     */
    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getModelNumber(): string
    {
        return $this->modelNumber;
    }

    /**
     * @param string $modelNumber
     */
    public function setModelNumber(string $modelNumber): void
    {
        $this->modelNumber = $modelNumber;
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }
}
