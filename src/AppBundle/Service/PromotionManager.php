<?php


namespace AppBundle\Service;


use AppBundle\Entity\Category;
use AppBundle\Entity\Offer;
use AppBundle\Repository\PromotionRepository;

class PromotionManager
{
    protected $generalPromotion;
    protected $categoryPromotions;
    protected $offerPromotions;

    public function __construct(PromotionRepository $repo)
    {
        $this->generalPromotion=$repo->findBiggestGeneralPromotion();
        $this->categoryPromotions=$repo->findCategoriesPromotions();
        $this->offerPromotions=$repo->findOffersPromotions();
    }

    public function getGeneralPromotion()
    {
        return $this->generalPromotion ?? 0;
    }

    public function hasCategoryPromotion(Category $category)
    {
        return array_key_exists($category->getId(), $this->categoryPromotions);
    }

    public function getCategoryPromotion(Category $category)
    {
        return $this->categoryPromotions[$category->getId()];
    }

    public function hasOfferPromotion(Offer $offer)
    {
        return array_key_exists($offer->getId(), $this->offerPromotions);
    }
    public function getOfferPromotion(Offer $offer)
    {
        return $this->offerPromotions[$offer->getId()];
    }
}