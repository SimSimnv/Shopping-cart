<?php

namespace AppBundle\Controller\Administration;



use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/administration/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="admin_users_list")
     */
    public function usersIndexAction()
    {
        $users=$this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('administration/users/list.html.twig', ['users'=>$users]);
    }

    /**
     * @Route("/{id}/edit", name="admin_users_edit")
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

        if($form->isSubmitted() && $form->isValid()){
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
}
