<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommuterMatch
 *
 * @ORM\Table(name="commuter_match", indexes={@ORM\Index(name="passenger_idx", columns={"passenger"}), @ORM\Index(name="driver_idx", columns={"driver"})})
 * @ORM\Entity
 */
#[ORM\Entity]
class CommuterMatch
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
     * @var int|null
     *
     * @ORM\Column(name="total_trip", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $totalTrip;

    /**
     * @var int|null
     *
     * @ORM\Column(name="distance_home", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $distanceHome;

    /**
     * @var int|null
     *
     * @ORM\Column(name="distance_work", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $distanceWork;

    /**
     * @var int|null
     *
     * @ORM\Column(name="duration_home", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $durationHome;

    /**
     * @var int|null
     *
     * @ORM\Column(name="duration_work", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $durationWork;


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
     * @ORM\Column(name="additional_time", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private $additionalTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="driver_status", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private $driverStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passenger_status", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private $passengerStatus;

    /**
     * @var Commuter
     *
     * @ORM\ManyToOne(targetEntity="Commuter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="driver", referencedColumnName="id")
     * })
     */
    #[ORM\ManyToOne(targetEntity: "Commuter")]
    #[ORM\JoinColumn(name: "driver", referencedColumnName: "id")]
    private $driver;

    /**
     * @var Commuter
     *
     * @ORM\ManyToOne(targetEntity="Commuter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     * })
     */
    #[ORM\ManyToOne(targetEntity: "Commuter")]
    #[ORM\JoinColumn(name: "passenger", referencedColumnName: "id")]
    private $passenger;

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
     * @return int|null
     */
    public function getTotalTrip(): ?int
    {
        return $this->totalTrip;
    }

    /**
     * @param int|null $totalTrip
     */
    public function setTotalTrip(?int $totalTrip): void
    {
        $this->totalTrip = $totalTrip;
    }

    /**
     * @return int|null
     */
    public function getDistanceHome(): ?int
    {
        return $this->distanceHome;
    }

    /**
     * @param int|null $distanceHome
     */
    public function setDistanceHome(?int $distanceHome): void
    {
        $this->distanceHome = $distanceHome;
    }

    /**
     * @return int|null
     */
    public function getDistanceWork(): ?int
    {
        return $this->distanceWork;
    }

    /**
     * @param int|null $distanceWork
     */
    public function setDistanceWork(?int $distanceWork): void
    {
        $this->distanceWork = $distanceWork;
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
    public function getAdditionalTime(): ?string
    {
        return $this->additionalTime;
    }

    /**
     * @param string|null $additionalTime
     */
    public function setAdditionalTime(?string $additionalTime): void
    {
        $this->additionalTime = $additionalTime;
    }

    /**
     * @return string|null
     */
    public function getDriverStatus(): ?string
    {
        return $this->driverStatus;
    }

    /**
     * @param string|null $driverStatus
     */
    public function setDriverStatus(?string $driverStatus): void
    {
        $this->driverStatus = $driverStatus;
    }

    /**
     * @return string|null
     */
    public function getPassengerStatus(): ?string
    {
        return $this->passengerStatus;
    }

    /**
     * @param string|null $passengerStatus
     */
    public function setPassengerStatus(?string $passengerStatus): void
    {
        $this->passengerStatus = $passengerStatus;
    }

    /**
     * @return Commuter
     */
    public function getDriver(): Commuter
    {
        return $this->driver;
    }

    /**
     * @param Commuter $driver
     */
    public function setDriver(Commuter $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return Commuter
     */
    public function getPassenger(): Commuter
    {
        return $this->passenger;
    }

    /**
     * @param Commuter $passenger
     */
    public function setPassenger(Commuter $passenger): void
    {
        $this->passenger = $passenger;
    }

    /**
     * @return int|null
     */
    public function getDurationHome(): ?int
    {
        return $this->durationHome;
    }

    /**
     * @param int|null $durationHome
     */
    public function setDurationHome(?int $durationHome): void
    {
        $this->durationHome = $durationHome;
    }

    /**
     * @return int|null
     */
    public function getDurationWork(): ?int
    {
        return $this->durationWork;
    }

    /**
     * @param int|null $durationWork
     */
    public function setDurationWork(?int $durationWork): void
    {
        $this->durationWork = $durationWork;
    }


}
