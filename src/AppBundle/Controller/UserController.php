<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/users")
 * @Security("has_role('ROLE_USER')")
 */
class UserController extends Controller
{
    /**
     * @Route("/{name}", name="user_profile")
     */
    public function indexAction($name)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->findOneBy(['username'=>$name]);

        return $this->render('main/users/profile.html.twig',['user'=>$user]);
    }
}
