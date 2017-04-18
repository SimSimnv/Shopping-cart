<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\OfferType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class OfferController extends Controller
{
    /**
     * @Route("/offers", name="offers_list")
     *
     */
    public function indexAction()
    {
        $offers=$this->getDoctrine()->getRepository(Offer::class)->findAll();
        return $this->render('offers/list.html.twig', ['offers'=>$offers]);
    }

    /**
     * @Route("/offers/create/{product_id}", name="offers_create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request, $product_id)
    {
            $product=$this->getDoctrine()->getRepository(Product::class)->find($product_id);
            $offer=new Offer();
            $form=$this->createForm(OfferType::class,$offer);

            $quantity=[];
            for ($i=1; $i<=$product->getQuantity(); $i++){
                $quantity[$i]=$i;
            }

            $form->add(
                'quantity',
                ChoiceType::class,
                [
                    'choices'=>$quantity,
                ]
            );

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $quantity=$offer->getQuantity();
                if($quantity>$product->getQuantity()){
                    $this->addFlash('error','You don\'t have enough '.$product->getName());
                    return $this->render('offers/create.html.twig',['create_form'=>$form->createView()]);
                }



                $offerProduct=new Product();
                $offerProduct->setName($product->getName());
                $offerProduct->setQuantity($quantity);

                $offer->setProduct($offerProduct);
                $offer->setUser($this->getUser());

                $em=$this->getDoctrine()->getManager();

                $product->reduceQuantity($quantity);
                if($product->getQuantity()==0){
                    $em->remove($product);
                }

                $em->persist($offerProduct);
                $em->persist($offer);
                $em->flush();



                $this->addFlash('success','Offer created!');
                return $this->redirectToRoute('offers_list');
            }

            return $this->render('offers/create.html.twig',['create_form'=>$form->createView()]);
    }

    /**
     * @Route("/offers/{id}", name="offers_details", requirements={"id": "\d+"})
     */
    public function detailsAction(Offer $offer)
    {

        return $this->render('offers/details.html.twig',[
            'offer'=>$offer,
            'product'=>$offer->getProduct()
        ]);
    }

    /**
     * @Route("offers/{id}/buy", name="offers_buy")
     * @Method("POST")
     *  @Security("has_role('ROLE_USER')")
     */
    public function buyAction(Request $request, Offer $offer)
    {
        $amount=$request->request->get('amount');
        $product=$offer->getProduct();
        if($amount>$product->getQuantity()){
            $this->addFlash('error','Invalid amount!');
            return $this->redirectToRoute('offers_details',['id'=>$offer->getId()]);
        }

        $price=$offer->getPrice()*$amount;

        /**@var $user User**/
        $user=$this->getUser();

        if ($user->getMoney()<$price){
            $this->addFlash('error','You don\'t have enough money!');
            return $this->redirectToRoute('offers_details',['id'=>$offer->getId()]);
        }

        $product->reduceQuantity($amount);
        $offer->getUser()->increaseMoney($price);

        $purchasedProduct=new Product();
        $purchasedProduct->setName($product->getName());
        $purchasedProduct->setUser($user);
        $purchasedProduct->setQuantity($amount);

        $user->addProduct($product);
        $user->reduceMoney($price);

        $em=$this->getDoctrine()->getManager();

        if($product->getQuantity()==0){
            $em->remove($product);
            $em->remove($offer);
        }

        $em->persist($purchasedProduct);
        $em->flush();

        $this->addFlash('success','Purchase successful!');
        return $this->redirectToRoute('products_list');

    }
    /**
     * @Route("offers/{id}/cancel", name="offers_cancel")
     * @Security("has_role('ROLE_USER')")
     */
    public function cancelAction(Offer $offer)
    {
        /**@var $user User**/
        $user=$this->getUser();

        if($offer->getUser()->getId() != $user->getId()){
            $this->addFlash('error','Not your offer!');
            return $this->redirectToRoute('offers_details',['id'=>$offer->getId()]);
        }

        $offerProduct=$offer->getProduct();

        $userProduct=new Product();
        $userProduct->setName($offerProduct->getName());
        $userProduct->setQuantity($offerProduct->getQuantity());
        $userProduct->setUser($user);

        $user->addProduct($offerProduct);
        $em=$this->getDoctrine()->getManager();
        $em->remove($offer);
        $em->remove($offerProduct);
        $em->persist($userProduct);
        $em->flush();
        $this->addFlash('success','Offer canceled');
        return $this->redirectToRoute('products_list');
    }
}
