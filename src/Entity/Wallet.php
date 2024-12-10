<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: 'wallets')]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'wallet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 65, scale: 2)]
    private ?string $currency = null;

    /**
     * @var Collection<int, TransactionHistory>
     */
    #[ORM\OneToMany(targetEntity: TransactionHistory::class, mappedBy: 'wallet', orphanRemoval: true)]
    private Collection $transactionHistories;

    public function __construct()
    {
        $this->currency = 0;
        $this->transactionHistories = new ArrayCollection();
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

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Collection<int, TransactionHistory>
     */
    public function getTransactionHistories(): Collection
    {
        return $this->transactionHistories;
    }

    public function addTransactionHistory(TransactionHistory $transactionHistory): static
    {
        if (!$this->transactionHistories->contains($transactionHistory)) {
            $this->transactionHistories->add($transactionHistory);
            $transactionHistory->setWallet($this);
        }

        return $this;
    }

    public function removeTransactionHistory(TransactionHistory $transactionHistory): static
    {
        if ($this->transactionHistories->removeElement($transactionHistory)) {
            if ($transactionHistory->getWallet() === $this) {
                $transactionHistory->setWallet(null);
            }
        }

        return $this;
    }
}
