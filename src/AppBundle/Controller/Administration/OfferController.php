<?php

namespace AppBundle\Controller\Administration;


use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
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
        $query=$this
            ->getDoctrine()
            ->getRepository(Offer::class)
            ->createQueryBuilder('o')
            ->select('o');

        $pagination=$paginator->paginate(
            $query->getQuery(),
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

        $editorForm=$this->createForm(OfferType::class,$offer);
        $editorForm
            ->remove('price')
            ->remove('title')
            ->remove('description');

        $quantity=[];
        for ($i=1; $i<=$product->getQuantity(); $i++){
            $quantity[$i]=$i;
        }

        $editorForm->add(
            'quantity',
            ChoiceType::class,
            [
                'choices'=>$quantity,
            ]
        );

        $editorForm->handleRequest($request);

        if($editorForm->isSubmitted() && $editorForm->isValid()){
            $offerQuantity=$offer->getQuantity();
            if($offerQuantity>$product->getQuantity() || $offerQuantity<1){
                $this->addFlash('error','Invalid amount');
                return $this->redirectToRoute('admin_offers_edit');
            }

            $em=$this->getDoctrine()->getManager();

            $returnedQuantity=$product->getQuantity()-$offerQuantity;
            if($returnedQuantity>0){
                /**@var  User**/
                $productOwner=$offer->getUser();
                $returnedProduct=new Product();
                $returnedProduct->setName($product->getName());
                $returnedProduct->setQuantity($returnedQuantity);
                $returnedProduct->setImage($product->getImage());
                $returnedProduct->setUser($productOwner);
                $productOwner->addProduct($returnedProduct);
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

        $offerProduct=$offer->getProduct();

        $userProduct=new Product();
        $userProduct->setName($offerProduct->getName());
        $userProduct->setQuantity($offerProduct->getQuantity());
        $userProduct->setImage($offerProduct->getImage());
        $userProduct->setUser($user);

        $user->addProduct($userProduct);
        $em=$this->getDoctrine()->getManager();
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
