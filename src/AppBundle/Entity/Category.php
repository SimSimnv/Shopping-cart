<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 * @UniqueEntity("name")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Enter category name!")
     */
    private $name;

    /**
     * @var Offer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Offer",mappedBy="category")
     */
    private $offers;

    /**
     * @var Promotion[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Promotion",mappedBy="category")
     */
    private $promotions;


    public function __construct()
    {
        $this->offers=new ArrayCollection();
        $this->promotions=new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Offer[]|ArrayCollection
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @param Offer[]|ArrayCollection $offers
     */
    public function setOffers($offers)
    {
        $this->offers = $offers;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Promotion[]|ArrayCollection
     */
    public function getPromotions()
    {
        return $this->promotions;
    }

    /**
     * @param Promotion[]|ArrayCollection $promotions
     */
    public function setPromotions($promotions)
    {
        $this->promotions = $promotions;
    }



}

