<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\ProductType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/administration")
 */
class AdministratorController extends Controller
{
    /**
     * @Route("/users", name="admin_users_list")
     */
    public function usersIndexAction()
    {
        $users=$this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('administration/users/list.html.twig', ['users'=>$users]);
    }

    /**
     * @Route("/users/{id}/edit", name="admin_users_edit")
     */
    public function usersEditAction(Request $request, User $user)
    {
        $userRole=$user->getHighestRole();
        $form=$this->createForm(UserType::class,$user);
        $form->add('money',MoneyType::class);
        $form->remove('password');
        $form->remove('email');

        if($userRole!='Administrator'){
            $form->add('promote',SubmitType::class);
        }
        if($userRole!='User'){
            $form->add('demote',SubmitType::class);
        }
        $form->add('edit',SubmitType::class);

        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();

            if($form->has('promote') && $form->get('promote')->isClicked()){
                $this->promoteUser($user);
                $em->flush();
                $this->addFlash('success','User '.$user->getUsername().' promoted!');
                return $this->redirectToRoute('admin_users_edit',['id'=>$user->getId()]);
            }
            else if($form->has('demote') &&$form->get('demote')->isClicked()){
                $this->demoteUser($user);
                $em->flush();
                $this->addFlash('success','User '.$user->getUsername().' demoted!');
                return $this->redirectToRoute('admin_users_edit',['id'=>$user->getId()]);
            }

            $em->flush();
            $this->addFlash('success','User edit successful!');
            return $this->redirectToRoute('admin_users_edit',['id'=>$user->getId()]);
        }

        return $this->render(
            'administration/users/edit.html.twig',
            [
                'user'=>$user,
                'edit_form'=>$form->createView()
            ]
        );

    }

    private function promoteUser(User $user)
    {
        if($user->getHighestRole()=='User'){
            $editorRole=$this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>'ROLE_EDITOR']);
            $user->addRole($editorRole);
        }
        else if($user->getHighestRole()=='Editor'){
            $adminRole=$this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>'ROLE_ADMIN']);
            $user->addRole($adminRole);
        }
    }
    private function demoteUser(User $user)
    {
        if($user->getHighestRole()=='Administrator'){
            $adminRole=$this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>'ROLE_ADMIN']);
            $user->removeRole($adminRole);
        }
        else if($user->getHighestRole()=='Editor'){
            $editorRole=$this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>'ROLE_EDITOR']);
            $user->removeRole($editorRole);
        }
    }

    /**
     * @Route("/users/{id}/products", name="admin_users_products")
     */
    public function productsListAction(Request $request, User $user)
    {
        $products=$this->getDoctrine()->getRepository(Product::class)->findBy(['user'=>$user]);
        return $this->render('administration/users/products.html.twig',[
            'user'=>$user,
            'products'=>$products
        ]);
    }

    /**
     * @Route("/users/{id}/products/create", name="admin_users_products_create")
     */
    public function productsCreateAction(Request $request, User $user)
    {
        $product=new Product();
        $form=$this->createForm(ProductType::class,$product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product->setUser($user);
            $em=$this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash('success','Product created!');
            return $this->redirectToRoute('admin_users_products',['id'=>$user->getId()]);
        }

        return $this->render("administration/users/create_product.html.twig",['create_form'=>$form->createView()]);
    }

    /**
     * @Route("/users/{id}/products/{product_id}/delete", name="admin_users_products_delete")
     */
    public function productsRemoveAction($id,$product_id)
    {
        $product=$this->getDoctrine()->getRepository(Product::class)->find($product_id);
        $em=$this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        $this->addFlash('success','Product removed!');
        return $this->redirectToRoute('admin_users_products',['id'=>$id]);
    }
}
