<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payments')]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: OrderHistory::class, inversedBy: 'payment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'order_history_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?OrderHistory $orderHistory = null;

    /**
     * Define payment method: 0 - Cash, 1 - VNPay, 2 - Spay Later
     *
     * @var int|null
     */
    #[ORM\Column(type: 'decimal', precision: 65, scale: 2)]
    private ?int $payment_method = null;

    /**
     * Define payment status: 0 - Pending, 1 - Completed, 2 - Failed
     *
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $paid_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $created_at;

    #[ORM\OneToOne(targetEntity: Transaction::class, mappedBy: 'payment', cascade: ['persist', 'remove'])]
    private ?Transaction $transaction = null;

    /**
     * @var Collection<int, Installment>
     */
    #[ORM\OneToMany(targetEntity: Installment::class, mappedBy: 'payment', orphanRemoval: true)]
    private Collection $installments;

    public function __construct()
    {
        $this->status = 0;
        $this->created_at = new DateTime();
        $this->installments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getOrderHistory(): ?OrderHistory
    {
        return $this->orderHistory;
    }

    public function setOrderHistory(?OrderHistory $orderHistory): void
    {
        $this->orderHistory = $orderHistory;
    }

    public function getPaymentMethod(): ?int
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(?int $payment_method): void
    {
        $this->payment_method = $payment_method;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    public function getPaidAt(): ?DateTimeInterface
    {
        return $this->paid_at;
    }

    public function setPaidAt(?DateTimeInterface $paid_at): void
    {
        $this->paid_at = $paid_at;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?DateTimeInterface $created_at): void
    {
        $this->created_at = $created_at;
    }


    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): static
    {
        if ($transaction->getPayment() !== $this) {
            $transaction->setPayment($this);
        }

        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return Collection<int, Installment>
     */
    public function getInstallments(): Collection
    {
        return $this->installments;
    }

    public function addInstallment(Installment $installment): static
    {
        if (!$this->installments->contains($installment)) {
            $this->installments->add($installment);
            $installment->setPayment($this);
        }

        return $this;
    }

    public function removeInstallment(Installment $installment): static
    {
        if ($this->installments->removeElement($installment)) {
            if ($installment->getPayment() === $this) {
                $installment->setPayment(null);
            }
        }

        return $this;
    }
}
