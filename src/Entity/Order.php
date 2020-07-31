<?php

namespace App\Entity;
use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     */
    private $adress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)     
     */
    private $adress_complement;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     */
    private $zip_code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message="Ne peut être vide."
     * )
     */
    private $phone_number;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity=DeliveryOrder::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\All({
     *        @Assert\Type(type="App\Entity\DeliveryOrder"),
     * })
     */
    private $DeliveryOrder;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\All({
     *        @Assert\Type(type="App\Entity\Product"),
     * })
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     */
    private $statusPayment = "WAITING";

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $methodPayment;

    /**
     * @ORM\OneToOne(targetEntity=Payment::class, mappedBy="client", cascade={"persist", "remove"})
     */
    private $payment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getAdressComplement(): ?string
    {
        return $this->adress_complement;
    }

    public function setAdressComplement(?string $adress_complement): self
    {
        $this->adress_complement = $adress_complement;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public function setZipCode(string $zip_code): self
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDeliveryOrder(): ?DeliveryOrder
    {
        return $this->DeliveryOrder;
    }

    public function setDeliveryOrder(DeliveryOrder $DeliveryOrder): self
    {
        $this->DeliveryOrder = $DeliveryOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getStatusPayment(): ?string
    {
        return $this->statusPayment;
    }

    public function setStatusPayment(string $statusPayment): self
    {
        $this->statusPayment = $statusPayment;

        return $this;
    }

    public function getMethodPayment(): ?string
    {
        return $this->methodPayment;
    }

    public function setMethodPayment(string $methodPayment): self
    {
        $this->methodPayment = $methodPayment;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;

        // set the owning side of the relation if necessary
        if ($payment->getClient() !== $this) {
            $payment->setClient($this);
        }

        return $this;
    }
}
