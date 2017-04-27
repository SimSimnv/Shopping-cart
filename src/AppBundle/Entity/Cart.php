<?php


namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;

class Cart
{
    /**
     * @var Offer[]|ArrayCollection
     */
    private $purchases;

    public function __construct()
    {
        $this->purchases=new ArrayCollection();
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
        foreach ($purchases as $purchase){
            $this->purchases->add($purchase);
        }
    }



}