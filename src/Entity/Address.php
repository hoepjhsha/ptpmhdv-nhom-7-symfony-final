<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Table(name: 'addresses')]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    private ?string $delivery_address = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $phone_number = null;

    #[ORM\Column(length: 255)]
    private ?string $zip_code = null;

    /**
     * @var Collection<int, OrderHistory>
     */
    #[ORM\OneToMany(targetEntity: OrderHistory::class, mappedBy: 'address', orphanRemoval: true)]
    private Collection $orderHistories;

    public function __construct()
    {
        $this->orderHistories = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->delivery_address;
    }

    public function setDeliveryAddress(string $delivery_address): static
    {
        $this->delivery_address = $delivery_address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public function setZipCode(string $zip_code): static
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    /**
     * @return Collection<int, OrderHistory>
     */
    public function getOrderHistories(): Collection
    {
        return $this->orderHistories;
    }

    public function addOrderHistory(OrderHistory $orderHistory): static
    {
        if (!$this->orderHistories->contains($orderHistory)) {
            $this->orderHistories->add($orderHistory);
            $orderHistory->setAddress($this);
        }

        return $this;
    }

    public function removeOrderHistory(OrderHistory $orderHistory): static
    {
        if ($this->orderHistories->removeElement($orderHistory)) {
            // set the owning side to null (unless already changed)
            if ($orderHistory->getAddress() === $this) {
                $orderHistory->setAddress(null);
            }
        }

        return $this;
    }
}
