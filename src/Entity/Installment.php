<?php

namespace App\Entity;

use App\Repository\InstallmentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstallmentRepository::class)]
#[ORM\Table(name: 'installments')]
class Installment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Payment::class, cascade: ['persist', 'remove'], inversedBy: 'installments')]
    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Payment $payment = null;

    #[ORM\Column]
    private ?int $installment_no = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 2)]
    private ?string $later_fee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $due_date = null;

    #[ORM\Column]
    private ?bool $paid;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 2)]
    private ?string $late_fee;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $created_at;

    #[ORM\OneToOne(targetEntity: Transaction::class, mappedBy: 'installment', cascade: ['persist', 'remove'])]
    private ?Transaction $transact = null;

    public function __construct()
    {
        $this->paid = false;
        $this->late_fee = '0.00';
        $this->created_at = new DateTime();
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

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getInstallmentNo(): ?int
    {
        return $this->installment_no;
    }

    public function setInstallmentNo(int $installment_no): static
    {
        $this->installment_no = $installment_no;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getLaterFee(): ?string
    {
        return $this->later_fee;
    }

    public function setLaterFee(?string $later_fee): void
    {
        $this->later_fee = $later_fee;
    }

    public function getDueDate(): ?DateTimeInterface
    {
        return $this->due_date;
    }

    public function setDueDate(DateTimeInterface $due_date): static
    {
        $this->due_date = $due_date;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getLateFee(): ?string
    {
        return $this->late_fee;
    }

    public function setLateFee(string $late_fee): static
    {
        $this->late_fee = $late_fee;

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

    public function getTransact(): ?Transaction
    {
        return $this->transact;
    }

    public function setTransact(?Transaction $transact): static
    {
        // unset the owning side of the relation if necessary
        if ($transact === null && $this->transact !== null) {
            $this->transact->setInstallment(null);
        }

        // set the owning side of the relation if necessary
        if ($transact !== null && $transact->getInstallment() !== $this) {
            $transact->setInstallment($this);
        }

        $this->transact = $transact;

        return $this;
    }
}
