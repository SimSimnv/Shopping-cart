<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Service\PriceCalculator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;


class CartController extends Controller
{
    /**
     * @Route("/cart", name="cart")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {

        /**@var $user User**/
        $user=$this->getUser();
        $purchases=$user->getPurchases();

        $form=$this->generateQuantitySelectForm($purchases);
        $form->handleRequest($request);

        $calc=$this->get('price_calculator');

        if($form->isSubmitted() && $form->isValid()){

            $em=$this->getDoctrine()->getManager();

            foreach ($form->getData() as $id=>$amount){
                $purchase=$purchases->filter(function (Offer $purchase) use ($id){return $purchase->getId()==$id;})->first();
                if($purchase!==false){
                    try{
                        $this->buyProduct($purchase,$amount,$em, $calc);
                        $purchases->removeElement($purchase);
                    } catch (\Exception $e){
                        $this->addFlash('error',$e->getMessage());
                        return $this->redirectToRoute('cart');
                    }
                }
            }

            $em->flush();
            $this->addFlash('success','Purchases successful!');
            return $this->redirectToRoute('products_list');
        }

        return $this->render('main/cart/main.html.twig', [
            'purchases'=>$purchases,
            'calc'=>$calc,
            'cart_form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/cart/{id}/cancel", name="cart_cancel")
     * @Security("has_role('ROLE_USER')")
     */
    public function cancelAction(Offer $purchase)
    {
        /**@var $user User**/
        $user=$this->getUser();
        $purchases=$user->getPurchases();
        $purchases->removeElement($purchase);
        $em=$this->getDoctrine()->getManager();
        $em->flush();
        $this->addFlash('success','Purchase removed!');
        return $this->redirectToRoute('cart');
    }

    private function buyProduct(Offer $offer, $amount,EntityManager $em, PriceCalculator $calc)
    {
        $product=$offer->getProduct();
        if($amount>$product->getQuantity()){
            throw new Exception('Invalid amount!');
        }

        $finalPrice=$calc->calculatePrice($offer);
        $price=$finalPrice*$amount;

        /**@var $user User**/
        $user=$this->getUser();

        if ($user->getMoney()<$price){
            throw new Exception('You don\'t have enough money!');
        }

        $product->reduceQuantity($amount);
        $offer->getUser()->increaseMoney($price);

        $purchasedProduct=new Product();
        $purchasedProduct->setName($product->getName());
        $purchasedProduct->setUser($user);
        $purchasedProduct->setQuantity($amount);
        $purchasedProduct->setImage($product->getImage());

        $user->addProduct($product);
        $user->reduceMoney($price);


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

        $em->persist($purchasedProduct);

    }

    private function generateQuantitySelectForm($purchases)
    {
        $formBuilder = $this->createFormBuilder([]);
        foreach ($purchases as $purchase){
            $purchaseId=$purchase->getId();
            $formBuilder->add(
                $purchaseId,
                ChoiceType::class,
                ['choices'=>$purchase->getProduct()->getQuantityArray()]
            );
        }
        $form=$formBuilder->getForm();
        return $form;
    }
}
