<?php


namespace AppBundle\Service;


use AppBundle\Entity\Offer;

class PriceCalculator
{

    protected $pm;

    protected $promotion;

    protected $genrePromotions = [];

    protected $offerPromotions = [];



    public function __construct(PromotionManager $pm)
    {
        $this->pm = $pm;
    }

    public function calculatePrice(Offer $offer)
    {
        $category = $offer->getCategory();

        $promotion = $this->pm->getGeneralPromotion();

        if ($this->pm->hasCategoryPromotion($category)) {
            $categoryPromotion = $this->pm->getCategoryPromotion($category);
            if ($categoryPromotion > $promotion) {
                $promotion = $categoryPromotion;
            }
        }
        if ($this->pm->hasOfferPromotion($offer)) {
            $offerPromotion = $this->pm->getOfferPromotion($offer);
            if($offerPromotion>$promotion){
                $promotion=$offerPromotion;
            }
        }

        $price = $offer->getPrice();

        return $price - $price * ($promotion / 100);
    }
}