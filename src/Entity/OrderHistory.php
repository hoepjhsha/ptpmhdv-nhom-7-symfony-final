<?php

namespace App\Entity;

use App\Repository\OrderHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderHistoryRepository::class)]
#[ORM\Table(name: 'order_histories')]
class OrderHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orderHistories')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    private array $order_items = [];

    /**
     * Define status of order: 0 - Pending, 1 - Processing, 2 - Completed, 3 - Cancelled
     *
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 2)]
    private ?string $total_price = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;


    /**
     * Define payment type of order: 0 - Cash, 1 - VNPay
     *
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $payment_type = null;

    #[ORM\OneToOne(targetEntity: Transaction::class, inversedBy: 'orderHistory', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'transact_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Transaction $transact = null;

    public function __construct()
    {
        $this->status = 0;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->total_price;
    }

    public function setTotalPrice(string $total_price): static
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getOrderItems(): array
    {
        return $this->order_items;
    }

    public function setOrderItems(array $order_items): static
    {
        $this->order_items = $order_items;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->payment_type;
    }

    public function setPaymentType(int $payment_type): static
    {
        $this->payment_type = $payment_type;

        return $this;
    }

    public function getTransact(): ?Transaction
    {
        return $this->transact;
    }

    public function setTransact(?Transaction $transact): static
    {
        $this->transact = $transact;

        return $this;
    }
}
