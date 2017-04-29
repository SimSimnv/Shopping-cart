<?php

namespace AppBundle\Controller\Administration;


use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserEditType;
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
    public function indexAction()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('administration/users/list.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/{id}/edit", name="admin_users_edit")
     */
    public function editAction(Request $request, User $user)
    {
        $form=$this->createForm(UserEditType::class,$user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($form->has('promote') && $form->get('promote')->isClicked()) {
                $this->promoteUser($user);
                $em->flush();
                $this->addFlash('success', 'User ' . $user->getUsername() . ' promoted!');
                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }
            else if ($form->has('demote') && $form->get('demote')->isClicked()) {
                $this->demoteUser($user);
                $em->flush();
                $this->addFlash('success', 'User ' . $user->getUsername() . ' demoted!');
                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $em->flush();
            $this->addFlash('success', 'User edit successful!');
            return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
        }

        return $this->render(
            'administration/users/edit.html.twig',
            [
                'user' => $user,
                'edit_form' => $form->createView()
            ]
        );

    }

    private function promoteUser(User $user)
    {
        if ($user->getHighestRole() == 'User') {
            $editorRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_EDITOR']);
            $user->addRole($editorRole);
        } else if ($user->getHighestRole() == 'Editor') {
            $adminRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN']);
            $user->addRole($adminRole);
        }
    }

    private function demoteUser(User $user)
    {
        if ($user->getHighestRole() == 'Administrator') {
            $adminRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN']);
            $user->removeRole($adminRole);
        } else if ($user->getHighestRole() == 'Editor') {
            $editorRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_EDITOR']);
            $user->removeRole($editorRole);
        }
    }

    /**
     * @Route("/{id}/ban", name="admin_users_ban")
     */
    public function banUser(Request $request, User $user)
    {
        if ($user->isBanned() == true) {
            $this->addFlash('error', 'User is already banned');
            return $this->redirectToRoute('admin_users_list');
        }
        $banForm = $this->createFormBuilder([])->getForm();
        $banForm->handleRequest($request);

        if ($banForm->isSubmitted() && $banForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($user->getOffers() as $offer) {
                $offer->setUser($this->getUser());
            }
            foreach ($user->getProducts() as $product) {
                $product->setUser($this->getUser());
            }
            $user->setBanned(true);
            $em->flush();
            $this->addFlash('success', 'User ' . $user->getUsername() . ' banned');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('administration/users/ban.html.twig', [
            'user' => $user,
            'ban_form' => $banForm->createView()
        ]);
    }

    /**
     * @Route("/{id}/unban", name="admin_users_unban")
     */
    public function unBan(Request $request, User $user)
    {
        if ($user->isBanned() == false) {
            $this->addFlash('error', 'User is not banned');
            return $this->redirectToRoute('admin_users_list');
        }
        $unBanForm = $this->createFormBuilder([])->getForm();
        $unBanForm->handleRequest($request);

        if ($unBanForm->isSubmitted() && $unBanForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setBanned(false);
            $em->flush();
            $this->addFlash('success', 'User ' . $user->getUsername() . ' unbanned');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('administration/users/unban.html.twig', [
            'user' => $user,
            'unban_form' => $unBanForm->createView()
        ]);
    }

}
