<?php

namespace AppBundle\Controller\Administration;

use AppBundle\Entity\Category;
use AppBundle\Entity\Offer;
use AppBundle\Entity\Promotion;
use AppBundle\Form\PromotionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
/**
 * @Security("has_role('ROLE_EDITOR')")
 * @Route("/administration")
 */
class PromotionController extends Controller
{
    /**
     * @Route("/offers/{id}/promotion", name="admin_offer_promotion")
     */
    public function offerAction(Request $request, Offer $offer)
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $today=(new \DateTime())->format('d-m-Y');
            $startDate=$promotion->getStartDate()->format('d-m-Y');
            $endDate=$promotion->getEndDate()->format('d-m-Y');
            if($startDate<$today || $endDate<$today || $endDate<=$startDate){
                $this->addFlash('error','Enter valid time range!');
                return $this->render('administration/promotions/offer.html.twig', [
                    'promo_form' => $form->createView(),
                    'offer'=>$offer
                ]);
            }
            $promotion->setOffer($offer);
            $em=$this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success','Promotion added!');
            return $this->redirectToRoute('admin_offers_list');
        }

        return $this->render('administration/promotions/offer.html.twig', [
            'promo_form' => $form->createView(),
            'offer'=>$offer
        ]);
    }

    /**
     * @Route("/categories/{id}/promotion", name="admin_category_promotion")
     */
    public function categoryAction(Request $request, Category $category)
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $today=(new \DateTime())->format('d-m-Y');
            $startDate=$promotion->getStartDate()->format('d-m-Y');
            $endDate=$promotion->getEndDate()->format('d-m-Y');
            if($startDate<$today || $endDate<$today || $endDate<=$startDate){
                $this->addFlash('error','Enter valid time range!');
                return $this->render('administration/promotions/category.html.twig', [
                    'promo_form' => $form->createView(),
                    'category'=>$category
                ]);
            }
            $promotion->setCategory($category);
            $em=$this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success','Promotion added!');
            return $this->redirectToRoute('categories_list');
        }

        return $this->render('administration/promotions/category.html.twig', [
            'promo_form' => $form->createView(),
            'category'=>$category
        ]);
    }

    /**
     * @Route("/promotion", name="admin_general_promotion")
     */
    public function generalAction(Request $request)
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $today=(new \DateTime())->format('d-m-Y');
            $startDate=$promotion->getStartDate()->format('d-m-Y');
            $endDate=$promotion->getEndDate()->format('d-m-Y');
            if($startDate<$today || $endDate<$today || $endDate<=$startDate){
                $this->addFlash('error','Enter valid time range!');
                return $this->render('administration/promotions/general.html.twig', [
                    'promo_form' => $form->createView()
                ]);
            }
            $em=$this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success','Promotion created!');
            return $this->redirectToRoute('categories_list');
        }

        return $this->render('administration/promotions/general.html.twig', [
            'promo_form' => $form->createView()
        ]);
    }
}
