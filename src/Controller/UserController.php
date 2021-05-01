<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController {
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function index() {

      $users = ['smth1', 'smth2', 'Bob?'];
      return $this->render('users/index.html.twig',['users' => $users]);
    }
}

?>