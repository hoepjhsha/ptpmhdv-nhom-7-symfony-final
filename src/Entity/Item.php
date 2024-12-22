<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToOne(targetEntity: Category::class, cascade: ['persist', 'remove'], inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'item_category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Category $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $item_image = null;

    #[ORM\Column(type: TYPES::TEXT, nullable: true)]
    private ?string $item_description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $updated_at;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'item', orphanRemoval: true)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?DateTimeInterface $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeInterface $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): static
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setItem($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            if ($cartItem->getItem() === $this) {
                $cartItem->setItem(null);
            }
        }

        return $this;
    }
}
