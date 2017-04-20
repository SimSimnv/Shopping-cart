<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Offer;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\CategoryType;
use AppBundle\Form\OfferType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_EDITOR')")
 * @Route("/administration")
 */
class EditorController extends Controller
{
    const BASE_CATEGORY='Other';

    /**
     * @Route("/", name="admin_homepage")
     */
    public function indexAction()
    {
        return $this->render('administration/home/index.html.twig');
    }

    /**
     * @Route("/categories", name="categories_list")
     */
    public function categoriesListAction()
    {
        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();
        return $this->render('administration/categories/list.html.twig',['categories'=>$categories]);
    }

    /**
     * @Route("/categories/create", name="categories_create")
     */
    public function categoriesCreateAction(Request $request)
    {
        $category=new Category();
        $form=$this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success','Category created!');
            return $this->redirectToRoute('categories_list');
        }
        return $this->render('administration/categories/create.html.twig',['form_create'=>$form->createView()]);
    }
    /**
     * @Route("/categories/{id}/remove", name="categories_remove")
     */
    public function categoriesRemoveAction(Category $category)
    {
        if($category->getName()==self::BASE_CATEGORY){
            $this->addFlash('error','Cannot delete the base category!');
            return $this->redirectToRoute('categories_list');
        }
        $offers=$category->getOffers();
        $defaultCategory=$this->getDoctrine()->getRepository(Category::class)->findOneBy(['name'=>self::BASE_CATEGORY]);

        foreach ($offers as $offer){
            $offer->setCategory($defaultCategory);
        }

        $em=$this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        $this->addFlash('success','Category removed!');
        return $this->redirectToRoute('categories_list');
    }

    /**
     * @Route("/offers", name="admin_offers_list")
     */
    public function offersListAction()
    {
        $offers=$this->getDoctrine()->getRepository(Offer::class)->findAll();
        return $this->render('administration/offers/list.html.twig',['offers'=>$offers]);
    }

    /**
     * @Route("/offers/{id}/edit", name="admin_offers_edit")
     */
    public  function offersEditAction(Request $request, Offer $offer)
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
                $returnedProduct->setUser($productOwner);
                $productOwner->addProduct($returnedProduct);
                $em->persist($returnedProduct);

                $product->reduceQuantity($returnedQuantity);
                if($product->getQuantity()==0){
                    $em->remove($product);
                    $em->remove($offer);
                }
            }


            $em->flush();

            $this->addFlash('success','Offer edited!');
            return $this->redirectToRoute('admin_offers_list');
        }

        return $this->render('administration/offers/edit.html.twig',[
            'offer'=>$offer,
            'product'=>$product,
            'edit_form'=>$editorForm->createView()
        ]);
    }

    /**
     * @Route("/offers/{id}/remove", name="admin_offers_remove")
     */
    public function offersRemoveAction(Offer $offer)
    {
        /**@var $user User**/
        $user=$offer->getUser();

        $offerProduct=$offer->getProduct();

        $userProduct=new Product();
        $userProduct->setName($offerProduct->getName());
        $userProduct->setQuantity($offerProduct->getQuantity());
        $userProduct->setUser($user);

        $user->addProduct($userProduct);
        $em=$this->getDoctrine()->getManager();
        $em->remove($offer);
        $em->remove($offerProduct);
        $em->persist($userProduct);
        $em->flush();
        $this->addFlash('success','Offer canceled');
        return $this->redirectToRoute('admin_offers_list');
    }
}
