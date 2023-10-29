<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommuterMatch
 *
 * @ORM\Table(name="commuter_match", indexes={@ORM\Index(name="passenger_idx", columns={"passenger"}), @ORM\Index(name="driver_idx", columns={"driver"})})
 * @ORM\Entity
 */
class CommuterMatch
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="total_trip", type="integer", nullable=true)
     */
    private $totalTrip;

    /**
     * @var int|null
     *
     * @ORM\Column(name="distance_home", type="integer", nullable=true)
     */
    private $distanceHome;

    /**
     * @var int|null
     *
     * @ORM\Column(name="distance_work", type="integer", nullable=true)
     */
    private $distanceWork;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=45, nullable=true)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="additional_time", type="string", length=45, nullable=true)
     */
    private $additionalTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="driver_status", type="string", length=45, nullable=true)
     */
    private $driverStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passenger_status", type="string", length=45, nullable=true)
     */
    private $passengerStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="matchcol", type="string", length=45, nullable=true)
     */
    private $matchcol;

    /**
     * @var \Commuter
     *
     * @ORM\ManyToOne(targetEntity="Commuter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="driver", referencedColumnName="id")
     * })
     */
    private $driver;

    /**
     * @var \Commuter
     *
     * @ORM\ManyToOne(targetEntity="Commuter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     * })
     */
    private $passenger;


}
