<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PassengerLiftClub
 *
 * @ORM\Table(name="passenger_lift_club", indexes={@ORM\Index(name="lift_club_idx", columns={"lift_club"}), @ORM\Index(name="passenger_fk_idx", columns={"passenger"})})
 * @ORM\Entity
 */
class PassengerLiftClub
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
     * @var \Liftclub
     *
     * @ORM\ManyToOne(targetEntity="Liftclub")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lift_club", referencedColumnName="id")
     * })
     */
    private $liftClub;

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
