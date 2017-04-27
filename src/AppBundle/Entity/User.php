<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface
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
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter username")
     * @Assert\Length(min="4",minMessage="Username must be at least 4 characters")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter email")
     * @Assert\Email(message="Please enter valid email")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\NotBlank(message="Please enter password")
     * @Assert\Length(min="5", minMessage="Password must be at least 5 characters")
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="money", type="decimal", precision=11, scale=2)
     */
    private $money;

    /**
     * @var Role[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="user_roles", joinColumns={@ORM\JoinColumn(name="user_id",referencedColumnName="id")}, inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")})
     */
    private $roles;

    /**
     * @var Product[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product",mappedBy="user")
     */
    private $products;


    /**
     * @var Offer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Offer", mappedBy="user")
     */
    private $offers;

    /**
     * @var Offer[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Offer", inversedBy="buyers")
     * @ORM\JoinTable(name="user_purchases", joinColumns={@ORM\JoinColumn(name="user_id",referencedColumnName="id")}, inverseJoinColumns={@ORM\JoinColumn(name="offer_id", referencedColumnName="id")})
     */
    private $purchases;

    /**
     * @var Review[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Review",mappedBy="user")
     */
    private $reviews;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_banned", type="boolean")
     */
    private $isBanned;

    public  function __construct()
    {
        $this->roles=new ArrayCollection();
        $this->products=new ArrayCollection();
        $this->offers=new ArrayCollection();
        $this->purchases=new ArrayCollection();
        $this->reviews=new ArrayCollection();
        $this->isBanned=false;
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set money
     *
     * @param string $money
     *
     * @return User
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get money
     *
     * @return string
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }





    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return array_map(function(Role $role){
            return $role->getName();
        },$this->roles->toArray());
    }
    public function addRole(Role $role){
        $this->roles->add($role);
    }
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }
    public function getHighestRole()
    {
        $roles=$this->getRoles();
        if(count($roles)==1){
            return 'User';
        }
        else if(count($roles)==2){
            return 'Editor';
        }
        else if(count($roles)==3){
            return 'Administrator';
        }
        return 'Invalid role';

    }
    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return Product[]|ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product[]|ArrayCollection $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }
    public function addProduct(Product $product)
    {
        $this->getProducts()->add($product);
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

    public function reduceMoney($amount)
    {
        $this->money-=$amount;
    }
    public function increaseMoney($amount)
    {
        $this->money+=$amount;
    }

    /**
     * @return Offer[]|ArrayCollection
     */
    public function getPurchases()
    {
        return $this->purchases;
    }

    /**
     * @param Offer[]|ArrayCollection $purchases
     */
    public function setPurchases($purchases)
    {
        $this->purchases = $purchases;
    }

    public function addPurchase(Offer $purchase)
    {
        $this->getPurchases()->add($purchase);
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
     * @return bool
     */
    public function isBanned()
    {
        return $this->isBanned;
    }

    /**
     * @param bool $isBanned
     */
    public function setBanned(bool $isBanned)
    {
        $this->isBanned = $isBanned;
    }



}

