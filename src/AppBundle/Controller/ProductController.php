<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends Controller
{

    /**
     * @Route("/products", name="products_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $products=$this->getDoctrine()->getRepository(Product::class)->findBy(['user'=>$this->getUser()]);
        return $this->render('main/products/list.html.twig',['products'=>$products]);
    }
    /**
     * @Route("/products/create", name="products_create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $product=new Product();
        $form=$this->createForm(ProductType::class,$product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product->setUser($this->getUser());
            $em=$this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash('success','Product created!');
            return $this->redirectToRoute('products_list');
        }

        return $this->render("main/products/create.html.twig",['create_form'=>$form->createView()]);
    }

    /**
     * @Route("/products/{id}/delete", name="products_delete", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function removeAction(Product $product)
    {
        if($product->getUser()->getId() != $this->getUser()->getId()){
            $this->addFlash('error','Can\'t remove other people\'s products!');
            return $this->redirectToRoute('products_list');
        }
        $em=$this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        $this->addFlash('success','Product removed!');
        return $this->redirectToRoute('products_list');
    }

}
