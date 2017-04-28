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
     * @Route("/promotions", name="promotions_list")
     */
    public function listAction()
    {
        $promotions = $this->getDoctrine()->getRepository(Promotion::class)->findAll();

        return $this->render('administration/promotions/list.html.twig', ['promotions' => $promotions]);
    }

    /**
     * @Route("/offers/{id}/promotion", name="admin_offer_promotion")
     */
    public function offerAction(Request $request, Offer $offer)
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeManager = $this->get('store_manager');

            if (!$storeManager->areDatesValid($promotion)) {
                $this->addFlash('error', 'Enter valid time range!');
                return $this->render('administration/promotions/offer.html.twig', [
                    'promo_form' => $form->createView(),
                    'offer' => $offer
                ]);
            }

            $promotion->setOffer($offer);
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success', 'Promotion added!');

            return $this->redirectToRoute('admin_offers_list');
        }

        return $this->render('administration/promotions/offer.html.twig', [
            'promo_form' => $form->createView(),
            'offer' => $offer
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
            $storeManager = $this->get('store_manager');
            if (!$storeManager->areDatesValid($promotion)) {
                $this->addFlash('error', 'Enter valid time range!');
                return $this->render('administration/promotions/category.html.twig', [
                    'promo_form' => $form->createView(),
                    'category' => $category
                ]);
            }

            $promotion->setCategory($category);
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success', 'Promotion added!');

            return $this->redirectToRoute('categories_list');
        }

        return $this->render('administration/promotions/category.html.twig', [
            'promo_form' => $form->createView(),
            'category' => $category
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
            $storeManager = $this->get('store_manager');

            if (!$storeManager->areDatesValid($promotion)) {
                $this->addFlash('error', 'Enter valid time range!');
                return $this->render('administration/promotions/general.html.twig', [
                    'promo_form' => $form->createView()
                ]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();
            $this->addFlash('success', 'Promotion created!');

            return $this->redirectToRoute('promotions_list');
        }

        return $this->render('administration/promotions/general.html.twig', [
            'promo_form' => $form->createView()
        ]);
    }

    /**
     * @Route("/promotions/{id}/remove", name="admin_promotions_remove")
     */
    public function removeAction(Promotion $promotion)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($promotion);
        $em->flush();
        $this->addFlash('success', 'Promotion removed');

        return $this->redirectToRoute('promotions_list');
    }

    /**
     * @Route("/promotions/{id}/edit", name="admin_promotions_edit")
     */
    public function editAction(Request $request, Promotion $promotion)
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $storeManager = $this->get('store_manager');

            if (!$storeManager->areDatesValid($promotion)) {
                $this->addFlash('error', 'Enter valid time range!');
                return $this->render('administration/promotions/edit.html.twig', [
                    'edit_form' => $form->createView(),
                ]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Promotion edited!');
            return $this->redirectToRoute('promotions_list');
        }

        return $this->render('administration/promotions/edit.html.twig', ['edit_form' => $form->createView()]);
    }
}