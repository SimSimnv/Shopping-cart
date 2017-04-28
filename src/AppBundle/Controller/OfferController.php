<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use AppBundle\Form\OfferType;
use AppBundle\Form\ReviewType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    const OFFERS_PER_PAGE = 6;

    /**
     * @Route("/offers", name="offers_list")
     *
     */
    public function indexAction(Request $request)
    {
        $query = $this->getDoctrine()->getRepository(Offer::class)->getSortedQuery();
        $page = $request->query->getInt('page', 1);

        return $this->listOffersResponse($query, $page);
    }

    /**
     * @Route("/offers/categories/{name}", name="offers_by_category")
     *
     */
    public function categoriesAction(Request $request, $name)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findBy(['name' => $name]);
        $query = $this
            ->getDoctrine()->getRepository(Offer::class)->getSortedQuery()
            ->where('o.category = :category')
            ->setParameter('category', $category);
        $page = $request->query->getInt('page', 1);

        return $this->listOffersResponse($query, $page, $name);

    }


    /**
     * @Route("/offers/{id}", name="offers_details", requirements={"id": "\d+"})
     */
    public function detailsAction(Offer $offer, Request $request)
    {
        $cartForm = $this->createFormBuilder([])->getForm();

        $review = new Review();
        $reviewForm = $this->createForm(ReviewType::class, $review);


        if ($request->request->has('form')) {
            $cartForm->handleRequest($request);
            if ($cartForm->isSubmitted() && $cartForm->isValid()) {
                try {
                    $this->addToCart($offer);
                    $this->addFlash('success', 'Added to cart!');
                    return $this->redirectToRoute('offers_list');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }
        if ($request->request->has('app_bundle_review_type')) {
            $reviewForm->handleRequest($request);
            if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
                $review->setUser($this->getUser());
                $review->setOffer($offer);
                $em = $this->getDoctrine()->getManager();
                $em->persist($review);
                $em->flush();
                $this->addFlash('success', 'Added review!');
                return $this->redirectToRoute('offers_details', ['id' => $offer->getId()]);
            }
        }


        $calc = $this->get('price_calculator');
        return $this->render('main/offers/details.html.twig', [
            'offer' => $offer,
            'product' => $offer->getProduct(),
            'calc' => $calc,
            'cart_form' => $cartForm->createView(),
            'review_form' => $reviewForm->createView()
        ]);
    }

    /**
     * @Route("offers/{id}/cancel", name="offers_cancel")
     * @Security("has_role('ROLE_USER')")
     */
    public function cancelAction(Offer $offer)
    {
        /**@var $user User* */
        $user = $this->getUser();

        if ($offer->getUser()->getId() != $user->getId()) {
            $this->addFlash('error', 'Not your offer!');
            return $this->redirectToRoute('offers_details', ['id' => $offer->getId()]);
        }

        $offerProduct = $offer->getProduct();
        $offerProduct->setUser($user);
        $em = $this->getDoctrine()->getManager();
        foreach ($offer->getReviews() as $review) {
            $em->remove($review);
        }
        foreach ($offer->getPromotions() as $promotion) {
            $em->remove($promotion);
        }
        $em->remove($offer);
        $em->flush();
        $this->addFlash('success', 'Offer canceled');
        return $this->redirectToRoute('products_list');
    }

    protected function addToCart(Offer $offer)
    {
        /**@var $user User* */
        $user = $this->getUser();
        if ($user->getPurchases()->contains($offer)) {
            throw new \Exception('Already in cart');
        }
        $user->addPurchase($offer);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    protected function listOffersResponse($query, $page, $selected = 'all'): Response
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $calc = $this->get('price_calculator');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            self::OFFERS_PER_PAGE
        );

        return $this->render(
            'main/offers/list.html.twig',
            [
                'categories' => $categories,
                'selected' => $selected,
                'calc' => $calc,
                'pagination' => $pagination
            ]);
    }

}
