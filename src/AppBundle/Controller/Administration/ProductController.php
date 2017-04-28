<?php

namespace AppBundle\Controller\Administration;


use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/administration/users")
 */
class ProductController extends Controller
{
    /**
     * @Route("/{id}/products", name="admin_users_products")
     */
    public function indexAction(Request $request, User $user)
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['user' => $user]);

        return $this->render('administration/products/list.html.twig', [
            'user' => $user,
            'products' => $products
        ]);
    }

    /**
     * @Route("/{id}/products/create", name="admin_users_products_create")
     */
    public function createAction(Request $request, User $user)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageUploader = $this->get('image_uploader');
            $imgLocation = $imageUploader->upload($product);
            $product->setImage($imgLocation);

            $product->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Product created!');

            return $this->redirectToRoute('admin_users_products', ['id' => $user->getId()]);
        }

        return $this->render("administration/products/create.html.twig", ['create_form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/products/{product_id}/delete", name="admin_users_products_delete")
     */
    public function removeAction($id, $product_id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($product_id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        $this->addFlash('success', 'Product removed!');

        return $this->redirectToRoute('admin_users_products', ['id' => $id]);
    }
}
