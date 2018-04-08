<?php
namespace Flownative\BestBuyProducts\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Category
{
    /**
     * @var string
     * @Flow\Identity
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(cascade={"persist"})
     * @var Category
     * @ORM\Column(nullable=true)
     */
    protected $parent;

    /**
     * Constructor
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Category
     */
    public function getParent(): ?Category
    {
        return $this->parent;
    }

    /**
     * @param Category $parent
     */
    public function setParent(Category $parent): void
    {
        $this->parent = $parent;
    }
}
