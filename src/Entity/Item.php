<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: 'items')]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $item_code = null;

    #[ORM\Column(length: 255)]
    private ?string $item_name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 2)]
    private ?string $item_price = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'item_category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Category $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $item_image = null;

    #[ORM\Column(type: TYPES::TEXT, nullable: true)]
    private ?string $item_description = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'item', orphanRemoval: true)]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getItemCode(): ?string
    {
        return $this->item_code;
    }

    public function setItemCode(string $item_code): static
    {
        $this->item_code = $item_code;

        return $this;
    }

    public function getItemName(): ?string
    {
        return $this->item_name;
    }

    public function setItemName(string $item_name): static
    {
        $this->item_name = $item_name;

        return $this;
    }

    public function getItemPrice(): ?string
    {
        return $this->item_price;
    }

    public function setItemPrice(string $item_price): static
    {
        $this->item_price = $item_price;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getItemImage(): ?string
    {
        return $this->item_image;
    }

    public function setItemImage(string $item_image): static
    {
        $this->item_image = $item_image;

        return $this;
    }

    public function getItemDescription(): ?string
    {
        return $this->item_description;
    }

    public function setItemDescription(?string $item_description): static
    {
        $this->item_description = $item_description;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setItem($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getItem() === $this) {
                $orderItem->setItem(null);
            }
        }

        return $this;
    }
}
