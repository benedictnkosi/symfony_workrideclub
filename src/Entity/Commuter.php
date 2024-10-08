<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commuter
 *
 * @ORM\Table(name="commuter", indexes={@ORM\Index(name="home_address_idx", columns={"home_address"}), @ORM\Index(name="work_address_idx", columns={"work_address"})})
 * @ORM\Entity
 */
#[ORM\Entity]
class Commuter
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
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 200, nullable: true)]
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="work_departure", type="string", length=45, nullable=true)
     */

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private $workDeparture;
    /**
     * @var string|null
     *
     * @ORM\Column(name="work_departure", type="string", length=10, nullable=true)
     */
    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private $homeDeparture;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fuel", type="string", length=10, nullable=true)
     */
    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private $fuel;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    #[ORM\Column(type: "datetime", nullable: true)]
    private $created;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_match", type="datetime", nullable=true)
     */
    #[ORM\Column(type: "datetime", nullable: true)]
    private $lastMatch;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private $type;

    /**
     * @var int|null
     *
     * @ORM\Column(name="type", type="integer", length=11, nullable=true)
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private ?int $travelTime;

    /**
     * @var CommuterAddress
     *
     * @ORM\ManyToOne(targetEntity="CommuterAddress")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="home_address", referencedColumnName="id")
     * })
     */
    #[ORM\ManyToOne(targetEntity: "CommuterAddress")]
    #[ORM\JoinColumn(name: "home_address", referencedColumnName: "id")]
    private $homeAddress;

    /**
     * @var CommuterAddress
     *
     * @ORM\ManyToOne(targetEntity="CommuterAddress")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="work_address", referencedColumnName="id")
     * })
     */
    #[ORM\ManyToOne(targetEntity: "CommuterAddress")]
    #[ORM\JoinColumn(name: "work_address", referencedColumnName: "id")]
    private $workAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="verification_code", type="string", length=255, nullable=true)
     */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $verificationCode;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="verification_code_expiry", type="datetime", nullable=true)
     */
    #[ORM\Column(type: "datetime", nullable: true)]
    private $verificationCodeExpiry;

    /**
     * @return \DateTime|null
     */
    public function getVerificationCodeExpiry(): ?\DateTime
    {
        return $this->verificationCodeExpiry;
    }

    /**
     * @param \DateTime|null $verificationCodeExpiry
     */
    public function setVerificationCodeExpiry(?\DateTime $verificationCodeExpiry): void
    {
        $this->verificationCodeExpiry = $verificationCodeExpiry;
    }
    /**
     * @return string|null
     */
    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    /**
     * @param string|null $verificationCode
     */
    public function setVerificationCode(?string $verificationCode): void
    {
        $this->verificationCode = $verificationCode;
    }

    /**
     * @var string|null
     *
     * @ORM\Column(name="guid", type="guid", nullable=true)
     */
    #[ORM\Column(type: "guid", nullable: true)]
    private $guid;

    /**
     * @return string|null
     */
    public function getGuid(): ?string
    {
        return $this->guid;
    }

    /**
     * @param string|null $guid
     */
    public function setGuid(?string $guid): void
    {
        $this->guid = $guid;
    }
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime|null $created
     */
    public function setCreated(?\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
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

    /**
     * @return CommuterAddress
     */
    public function getHomeAddress(): CommuterAddress
    {
        return $this->homeAddress;
    }

    /**
     * @param CommuterAddress $homeAddress
     */
    public function setHomeAddress(CommuterAddress $homeAddress): void
    {
        $this->homeAddress = $homeAddress;
    }

    /**
     * @return CommuterAddress
     */
    public function getWorkAddress(): CommuterAddress
    {
        return $this->workAddress;
    }

    /**
     * @param CommuterAddress $workAddress
     */
    public function setWorkAddress(CommuterAddress $workAddress): void
    {
        $this->workAddress = $workAddress;
    }

    /**
     * @return int|null
     */
    public function getTravelTime(): ?int
    {
        return $this->travelTime;
    }

    /**
     * @param int|null $travelTime
     */
    public function setTravelTime(?int $travelTime): void
    {
        $this->travelTime = $travelTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastMatch(): ?\DateTime
    {
        return $this->lastMatch;
    }

    /**
     * @param \DateTime|null $lastMatch
     */
    public function setLastMatch(?\DateTime $lastMatch): void
    {
        $this->lastMatch = $lastMatch;
    }

    /**
     * @return string|null
     */
    public function getWorkDeparture(): ?string
    {
        return $this->workDeparture;
    }

    /**
     * @param string|null $workDeparture
     */
    public function setWorkDeparture(?string $workDeparture): void
    {
        $this->workDeparture = $workDeparture;
    }

    /**
     * @return string|null
     */
    public function getHomeDeparture(): ?string
    {
        return $this->homeDeparture;
    }

    /**
     * @param string|null $homeDeparture
     */
    public function setHomeDeparture(?string $homeDeparture): void
    {
        $this->homeDeparture = $homeDeparture;
    }

    /**
     * @return string|null
     */
    public function getFuel(): ?string
    {
        return $this->fuel;
    }

    /**
     * @param string|null $fuel
     */
    public function setFuel(?string $fuel): void
    {
        $this->fuel = $fuel;
    }



}
