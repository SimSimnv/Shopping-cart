<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Offer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $offers = $this->getDoctrine()->getRepository(Offer::class)->findBy(['isFeatured' => true], ['createdOn' => 'DESC'], 4);
        $calc = $this->get('price_calculator');

        return $this->render('main/default/index.html.twig', [
            'offers' => $offers,
            'calc' => $calc
        ]);
    }

}
