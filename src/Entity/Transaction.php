<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transactions')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Payment::class, inversedBy: 'transaction', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Payment $payment = null;

    #[ORM\OneToOne(targetEntity: Installment::class, inversedBy: 'transact', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'installment_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Installment $installment = null;

    /**
     * Define transaction for: 0 - Payment, 1 - Installment
     *
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $transaction_for = null;

    #[ORM\Column(length: 255)]
    private ?string $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $bankCode = null;

    #[ORM\Column(length: 255)]
    private ?string $bankTranNo = null;

    #[ORM\Column(length: 255)]
    private ?string $cardType = null;

    #[ORM\Column(length: 255)]
    private ?string $orderInfo = null;

    #[ORM\Column(length: 255)]
    private ?string $payDate = null;

    #[ORM\Column(length: 255)]
    private ?string $responseCode = null;

    #[ORM\Column(length: 255)]
    private ?string $tmnCode = null;

    #[ORM\Column(length: 255)]
    private ?string $transactionNo = null;

    #[ORM\Column(length: 255)]
    private ?string $transactionStatus = null;

    #[ORM\Column(length: 255)]
    private ?string $txnRef = null;

    #[ORM\Column(length: 255)]
    private ?string $secureHash = null;

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

    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
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

    public function getBankCode(): ?string
    {
        return $this->bankCode;
    }

    public function setBankCode(string $bankCode): static
    {
        $this->bankCode = $bankCode;

        return $this;
    }

    public function getBankTranNo(): ?string
    {
        return $this->bankTranNo;
    }

    public function setBankTranNo(string $bankTranNo): static
    {
        $this->bankTranNo = $bankTranNo;

        return $this;
    }

    public function getCardType(): ?string
    {
        return $this->cardType;
    }

    public function setCardType(string $cardType): static
    {
        $this->cardType = $cardType;

        return $this;
    }

    public function getOrderInfo(): ?string
    {
        return $this->orderInfo;
    }

    public function setOrderInfo(string $orderInfo): static
    {
        $this->orderInfo = $orderInfo;

        return $this;
    }

    public function getPayDate(): ?string
    {
        return $this->payDate;
    }

    public function setPayDate(string $payDate): static
    {
        $this->payDate = $payDate;

        return $this;
    }

    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    public function setResponseCode(string $responseCode): static
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    public function getTmnCode(): ?string
    {
        return $this->tmnCode;
    }

    public function setTmnCode(string $tmnCode): static
    {
        $this->tmnCode = $tmnCode;

        return $this;
    }

    public function getTransactionNo(): ?string
    {
        return $this->transactionNo;
    }

    public function setTransactionNo(string $transactionNo): static
    {
        $this->transactionNo = $transactionNo;

        return $this;
    }

    public function getTransactionStatus(): ?string
    {
        return $this->transactionStatus;
    }

    public function setTransactionStatus(string $transactionStatus): static
    {
        $this->transactionStatus = $transactionStatus;

        return $this;
    }

    public function getTxnRef(): ?string
    {
        return $this->txnRef;
    }

    public function setTxnRef(string $txnRef): static
    {
        $this->txnRef = $txnRef;

        return $this;
    }

    public function getSecureHash(): ?string
    {
        return $this->secureHash;
    }

    public function setSecureHash(string $secureHash): static
    {
        $this->secureHash = $secureHash;

        return $this;
    }

    public function getInstallment(): ?Installment
    {
        return $this->installment;
    }

    public function setInstallment(?Installment $installment): static
    {
        $this->installment = $installment;

        return $this;
    }

    public function getTransactionFor(): ?int
    {
        return $this->transaction_for;
    }

    public function setTransactionFor(int $transaction_for): static
    {
        $this->transaction_for = $transaction_for;

        return $this;
    }
}
