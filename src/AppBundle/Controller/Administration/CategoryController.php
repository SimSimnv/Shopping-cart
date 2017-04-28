<?php

namespace AppBundle\Controller\Administration;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_EDITOR')")
 * @Route("/administration/categories")
 */
class CategoryController extends Controller
{
    const BASE_CATEGORY = 'Other';

    /**
     * @Route("/", name="categories_list")
     */
    public function indexAction()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('administration/categories/list.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/create", name="categories_create")
     */
    public function createAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Category created!');
            return $this->redirectToRoute('categories_list');
        }

        return $this->render('administration/categories/create.html.twig', ['form_create' => $form->createView()]);
    }

    /**
     * @Route("/{id}/remove", name="categories_remove")
     */
    public function removeAction(Category $category)
    {
        if ($category->getName() == self::BASE_CATEGORY) {
            $this->addFlash('error', 'Cannot delete the base category!');
            return $this->redirectToRoute('categories_list');
        }

        $offers = $category->getOffers();
        $defaultCategory = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['name' => self::BASE_CATEGORY]);

        foreach ($offers as $offer) {
            $offer->setCategory($defaultCategory);
        }

        $em = $this->getDoctrine()->getManager();
        foreach ($category->getPromotions() as $promotion) {
            $em->remove($promotion);
        }
        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Category removed!');
        return $this->redirectToRoute('categories_list');
    }

}
