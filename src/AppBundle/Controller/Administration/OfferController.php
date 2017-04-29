<?php

namespace AppBundle\Controller\Administration;


use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\OfferEditType;
use AppBundle\Form\OfferType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_EDITOR')")
 * @Route("/administration/offers")
 */
class OfferController extends Controller
{
    /**
     * @Route("/", name="admin_offers_list")
     */
    public function indexAction(Request $request)
    {
        $paginator=$this->get('knp_paginator');

        $pagination=$paginator->paginate(
            $this->getDoctrine()->getRepository(Offer::class)->getSortedQuery(),
            $request->query->getInt('page', 1),
            10
        );

        $calc=$this->get('price_calculator');
        return $this->render('administration/offers/list.html.twig',[
            'pagination'=>$pagination,
            'calc'=>$calc
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_offers_edit")
     */
    public  function editAction(Request $request, Offer $offer)
    {
        $product=$offer->getProduct();
        $offer->setQuantity($product->getQuantity());

        $editorForm=$this->createForm(OfferEditType::class,$offer);

        $editorForm->handleRequest($request);

        if($editorForm->isSubmitted() && $editorForm->isValid()){
            $offerQuantity=$offer->getQuantity();
            if($offerQuantity>$product->getQuantity() || $offerQuantity<1){
                $this->addFlash('error','Invalid amount');
                return $this->redirectToRoute('admin_offers_edit');
            }

            $storeManager=$this->get('store_manager');
            $em=$this->getDoctrine()->getManager();

            $returnedQuantity=$product->getQuantity()-$offerQuantity;
            if($returnedQuantity>0){
                /**@var  User**/
                $productOwner=$offer->getUser();

                $returnedProduct=$storeManager->cloneProduct($product,$productOwner,$returnedQuantity);
                $em->persist($returnedProduct);

                $product->reduceQuantity($returnedQuantity);
                if($product->getQuantity()==0){
                    foreach ($offer->getReviews() as $review){
                        $em->remove($review);
                    }
                    foreach ($offer->getPromotions() as $promotion) {
                        $em->remove($promotion);
                    }
                    $em->remove($product);
                    $em->remove($offer);
                }
            }


            $em->flush();

            $this->addFlash('success','Offer edited!');
            return $this->redirectToRoute('admin_offers_list');
        }
        $calc=$this->get('price_calculator');

        return $this->render('administration/offers/edit.html.twig',[
            'offer'=>$offer,
            'product'=>$product,
            'calc'=>$calc,
            'edit_form'=>$editorForm->createView()
        ]);
    }

    /**
     * @Route("/{id}/remove", name="admin_offers_remove")
     */
    public function removeAction(Offer $offer)
    {
        /**@var $user User**/
        $user=$offer->getUser();
        $storeManager=$this->get('store_manager');
        $em=$this->getDoctrine()->getManager();

        $offerProduct=$offer->getProduct();

        $userProduct=$storeManager->cloneProduct($offerProduct,$user,$offerProduct->getQuantity());

        foreach ($offer->getReviews() as $review){
            $em->remove($review);
        }
        foreach ($offer->getPromotions() as $promotion) {
            $em->remove($promotion);
        }

        $em->remove($offer);
        $em->remove($offerProduct);
        $em->persist($userProduct);
        $em->flush();
        $this->addFlash('success','Offer canceled');

        return $this->redirectToRoute('admin_offers_list');
    }
}
