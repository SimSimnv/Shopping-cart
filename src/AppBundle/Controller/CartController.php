<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Cart;
use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\CartType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;


class CartController extends Controller
{

    /**
     * @Route("/cart", name="cart")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {

        /**@var $user User* */
        $user = $this->getUser();
        $purchases = $user->getPurchases();

        $cart = new Cart();
        $cart->setPurchases($purchases);
        $cartForm = $this->createForm(CartType::class, $cart);
        $cartForm->handleRequest($request);


        if ($cartForm->isSubmitted() && $cartForm->isValid()) {
            try {
                $this->cartTransaction($cart);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $calc = $this->get('price_calculator');
        return $this->render('main/cart/main.html.twig', [
            'purchases' => $purchases,
            'calc' => $calc,
            'cart_form' => $cartForm->createView()
        ]);
    }


    /**
     * @Route("/cart/{id}/cancel", name="cart_cancel")
     * @Security("has_role('ROLE_USER')")
     */
    public function cancelAction(Offer $purchase)
    {
        /**@var $user User* */
        $user = $this->getUser();
        $purchases = $user->getPurchases();
        $purchases->removeElement($purchase);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        $this->addFlash('success', 'Purchase removed!');
        return $this->redirectToRoute('cart');
    }

    private function cartTransaction(Cart $cart)
    {
        $storeManager = $this->get('store_manager');
        $calc = $this->get('price_calculator');
        $em = $this->getDoctrine()->getManager();
        $userPurchases = $this->getUser()->getPurchases();


        foreach ($cart->getPurchases() as $offer) {
            /**@var $user User* */
            $user = $this->getUser();
            $product = $offer->getProduct();
            $amount = $offer->getQuantity();

            if ($amount > $product->getQuantity() || $amount < 1) {
                throw new Exception('Invalid amount!');
            }

            $finalPrice = $calc->calculatePrice($offer);
            $price = $finalPrice * $amount;

            if ($user->getMoney() < $price) {
                throw new Exception('You don\'t have enough money!');
            }

            $product->reduceQuantity($amount);
            $offer->getUser()->increaseMoney($price);

            $purchasedProduct = $storeManager->cloneProduct($product, $user, $amount);
            $user->reduceMoney($price);

            if ($product->getQuantity() == 0) {
                foreach ($offer->getReviews() as $review) {
                    $em->remove($review);
                }
                foreach ($offer->getPromotions() as $promotion) {
                    $em->remove($promotion);
                }
                $em->remove($product);
                $em->remove($offer);
            }


            $userPurchases->removeElement($offer);
            $em->persist($purchasedProduct);
        }

        $em->flush();
        $this->addFlash('success', 'Purchases successful!');
        return $this->redirectToRoute('products_list');
    }

}
