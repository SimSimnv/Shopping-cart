<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $encoder = $this->get('security.password_encoder');
            $passwordHash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($passwordHash);
            $user->setMoney(500);
            $role = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($role);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User ' . $user->getUsername() . " was registered successfully!");

            return $this->redirectToRoute("user_login");
        }
        return $this->render("main/security/register.html.twig", ['register_form' => $form->createView()]);
    }

    /**
     * @Route("/login", name="user_login")
     */
    public function loginAction()
    {
        $auth_utils = $this->get('security.authentication_utils');

        return $this->render("main/security/login.html.twig", [
            'last_username' => $auth_utils->getLastUsername(),
            'error' => $auth_utils->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("/logout", name="user_logout")
     */
    public function logoutAction()
    {

    }
}
