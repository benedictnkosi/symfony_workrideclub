<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PepperPrices
 *
 * @ORM\Table(name="pepper_prices")
 * @ORM\Entity
 */
#[ORM\Entity]
class PepperPrices
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
     * @ORM\Column(name="commodity", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 200, nullable: true)]
    private $commodity;

    /**
     * @var float|null
     *
     * @ORM\Column(name="weight", type="float", precision=10, scale=0, nullable=true)
     */
    #[ORM\Column(type: "float", length: 11, nullable: true)]
    private $weight;

    /**
     * @var float|null
     *
     * @ORM\Column(name="low", type="float", precision=10, scale=0, nullable=true)
     */
    #[ORM\Column(type: "float", length: 11, nullable: true)]
    private $low;

    /**
     * @var float|null
     *
     * @ORM\Column(name="high", type="float", precision=10, scale=0, nullable=true)
     */
    #[ORM\Column(type: "float", length: 11, nullable: true)]
    private $high;

    /**
     * @var float|null
     *
     * @ORM\Column(name="average", type="float", precision=10, scale=0, nullable=true)
     */
    #[ORM\Column(type: "float", length: 11, nullable: true)]
    private $average;

    /**
     * @var int|null
     *
     * @ORM\Column(name="sales_total", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private $salesTotal;

    /**
     * @var int|null
     *
     * @ORM\Column(name="total_kg_sold", type="integer", nullable=true)
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private $totalKgSold;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    #[ORM\Column(type: "datetime", nullable: true)]
    private $date;

    /**
     * @var string|null
     *
     * @ORM\Column(name="commodity", type="string", length=45, nullable=true)
     */
    #[ORM\Column(type: "string", length: 200, nullable: true)]
    private $container;

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
    public function getCommodity(): ?string
    {
        return $this->commodity;
    }

    /**
     * @param string|null $commodity
     */
    public function setCommodity(?string $commodity): void
    {
        $this->commodity = $commodity;
    }

    /**
     * @return float|null
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * @param float|null $weight
     */
    public function setWeight(?float $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return float|null
     */
    public function getLow(): ?float
    {
        return $this->low;
    }

    /**
     * @param float|null $low
     */
    public function setLow(?float $low): void
    {
        $this->low = $low;
    }

    /**
     * @return float|null
     */
    public function getHigh(): ?float
    {
        return $this->high;
    }

    /**
     * @param float|null $high
     */
    public function setHigh(?float $high): void
    {
        $this->high = $high;
    }

    /**
     * @return float|null
     */
    public function getAverage(): ?float
    {
        return $this->average;
    }

    /**
     * @param float|null $average
     */
    public function setAverage(?float $average): void
    {
        $this->average = $average;
    }

    /**
     * @return int|null
     */
    public function getSalesTotal(): ?int
    {
        return $this->salesTotal;
    }

    /**
     * @param int|null $salesTotal
     */
    public function setSalesTotal(?int $salesTotal): void
    {
        $this->salesTotal = $salesTotal;
    }

    /**
     * @return int|null
     */
    public function getTotalKgSold(): ?int
    {
        return $this->totalKgSold;
    }

    /**
     * @param int|null $totalKgSold
     */
    public function setTotalKgSold(?int $totalKgSold): void
    {
        $this->totalKgSold = $totalKgSold;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string|null
     */
    public function getContainer(): ?string
    {
        return $this->container;
    }

    /**
     * @param string|null $container
     */
    public function setContainer(?string $container): void
    {
        $this->container = $container;
    }

}
