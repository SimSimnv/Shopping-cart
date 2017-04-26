<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Promotion;
use AppBundle\Form\PromotionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class PromotionController extends Controller
{
    /**
     * @Route("/offers/{id}/promotion", name="offer_promotion")
     * @Security("has_role('ROLE_USER')")
     */
    public function offerAction(Request $request, Offer $offer)
    {
        if($offer->getUser()->getId()!= $this->getUser()->getId()){
            $this->addFlash('error','Not your offer!');
            return $this->redirectToRoute('offers_details',['id'=>$offer->getId()]);
        }

        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $today=(new \DateTime())->format('d-m-Y');
            $startDate=$promotion->getStartDate()->format('d-m-Y');
            $endDate=$promotion->getEndDate()->format('d-m-Y');
            if($startDate<$today || $endDate<$today || $endDate<=$startDate){
                $this->addFlash('error','Enter valid time range!');
                return $this->render('main/promotion/offer.html.twig', [
                    'promo_form' => $form->createView(),
                    'offer'=>$offer
                ]);
            }
            $promotion->setOffer($offer);
            $em=$this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success','Promotion added!');
            return $this->redirectToRoute('offers_details',['id'=>$offer->getId()]);
        }

        return $this->render('main/promotion/offer.html.twig', [
            'promo_form' => $form->createView(),
            'offer'=>$offer
        ]);
    }
}
