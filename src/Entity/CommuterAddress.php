<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommuterAddress
 *
 * @ORM\Table(name="commuter_address")
 * @ORM\Entity()
 */
#[ORM\Entity]
class CommuterAddress
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    #[ORM\Id, ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="full_address", type="string", length=100, nullable=true)
     */
    #[ORM\Column(type: "string", length: 200, nullable: true)]
    private $fullAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="city", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(name="state", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $state;

    /**
     * @var string|null
     *
     * @ORM\Column(name="latitude", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $latitude;

    /**
     * @var string|null
     *
     * @ORM\Column(name="longitude", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $longitude;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $type;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    /**
     * @param string|null $fullAddress
     */
    public function setFullAddress(?string $fullAddress): void
    {
        $this->fullAddress = $fullAddress;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string|null
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * @param string|null $latitude
     */
    public function setLatitude(?string $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return string|null
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * @param string|null $longitude
     */
    public function setLongitude(?string $longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }


}
