<?php


namespace AppBundle\Service;


use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;

class StoreManager
{
    public function cloneProduct(Product $product, User $user, $amount):Product
    {
        $newProduct=new Product();
        $newProduct->setName($product->getName());
        $newProduct->setQuantity($amount);
        $newProduct->setImage($product->getImage());
        $newProduct->setUser($user);
        return $newProduct;
    }

    public function areDatesValid(Promotion $promotion)
    {
        $today=(new \DateTime())->format('d-m-Y');
        $startDate=$promotion->getStartDate()->format('d-m-Y');
        $endDate=$promotion->getEndDate()->format('d-m-Y');
        if($startDate<$today || $endDate<$today || $endDate<=$startDate){
            return false;
        }
        return true;
    }
}