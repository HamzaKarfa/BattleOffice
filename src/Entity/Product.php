<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $info_sup;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="product")
     */
    private $orders;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $originPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPopulaire;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOfProduct;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberOffrer;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getInfoSup(): ?string
    {
        return $this->info_sup;
    }

    public function setInfoSup(string $info_sup): self
    {
        $this->info_sup = $info_sup;

        return $this;
    }
    public function __toString()
    {
        return  strval($this->getName());
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setProduct($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getProduct() === $this) {
                $order->setProduct(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getOriginPrice(): ?string
    {
        return $this->originPrice;
    }

    public function setOriginPrice(string $originPrice): self
    {
        $this->originPrice = $originPrice;

        return $this;
    }

    public function getIsPopulaire(): ?bool
    {
        return $this->isPopulaire;
    }

    public function setIsPopulaire(bool $isPopulaire): self
    {
        $this->isPopulaire = $isPopulaire;

        return $this;
    }

    public function getNumberOfProduct(): ?int
    {
        return $this->numberOfProduct;
    }

    public function setNumberOfProduct(int $numberOfProduct): self
    {
        $this->numberOfProduct = $numberOfProduct;

        return $this;
    }

    public function getNumberOffrer(): ?int
    {
        return $this->numberOffrer;
    }

    public function setNumberOffrer(int $numberOffrer): self
    {
        $this->numberOffrer = $numberOffrer;

        return $this;
    }
}
