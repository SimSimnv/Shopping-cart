<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Offer
 *
 * @ORM\Table(name="offers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OfferRepository")
 */
class Offer
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
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Please enter title")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=11, scale=2)
     * @Assert\NotBlank(message="Please enter price")
     * @Assert\GreaterThan(value="0",message="Price must be a positive number")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank(message="Please write description")
     */
    private $description;


    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User",inversedBy="offers")
     */
    private $user;

    /**
     * @var Product
     * @OneToOne(targetEntity="AppBundle\Entity\Product")
     * @JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime")
     */
    private $createdOn;

    private $quantity=1;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category",inversedBy="offers")
     * @Assert\NotNull(message="Please select a category")
     */
    private $category;


    /**
     * @var User[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="purchases")
     */
    private $buyers;

    /**
     * @var Review[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Review",mappedBy="offer")
     */
    private $reviews;

    /**
     * @var Promotion[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Promotion", mappedBy="offer")
     */
    private $promotions;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_featured", type="boolean")
     */
    private $isFeatured;

    public function __construct()
    {
        $this->buyers=new ArrayCollection();
        $this->reviews=new ArrayCollection();
        $this->promotions=new ArrayCollection();
        $this->createdOn=new \DateTime();
        $this->isFeatured=false;
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
     * Set title
     *
     * @param string $title
     *
     * @return Offer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Offer
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Offer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getBuyers()
    {
        return $this->buyers;
    }

    /**
     * @param User[]|ArrayCollection $buyers
     */
    public function setBuyers($buyers)
    {
        $this->buyers = $buyers;
    }

    /**
     * @return Review[]|ArrayCollection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param Review[]|ArrayCollection $reviews
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
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

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return bool
     */
    public function isIsFeatured()
    {
        return $this->isFeatured;
    }

    /**
     * @param bool $isFeatured
     */
    public function setIsFeatured(bool $isFeatured)
    {
        $this->isFeatured = $isFeatured;
    }




}


