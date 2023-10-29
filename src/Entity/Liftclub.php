<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Liftclub
 *
 * @ORM\Table(name="liftclub")
 * @ORM\Entity
 */
class Liftclub
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
     * @ORM\Column(name="driver", type="integer", nullable=true)
     */
    private $driver;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passenger", type="string", length=45, nullable=true)
     */
    private $passenger;


}
