<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserController extends AbstractController {
    /**
     * @Route("/users", name="user_list")
     * @Method({"GET"})
     */
    public function index() {

      $users = $this->getDoctrine()->getRepository(User::Class)->findAll();
      return $this->render('users/index.html.twig',['users' => $users]);
    }
    
    /**
     * @Route("/users/user/new", name="new_user")
     * Method({"GET", "POST"})
     */
    public function newUser(Request $request) {

      $user = new User();
      $form = $this->createFormBuilder($user) 
      ->add('name', TextType::class, ['attr' =>['class' => 'form-control']])
      ->add('save', SubmitType::class, ['label' => 'Create', 'attr' =>['class' => 'btn btn-primary mt-3']])
      ->getForm();
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'User was succesfully created');

        return $this->redirectToRoute('user_list');
      }
      return $this->render('users/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/user/edit/{id}", name="edit_user")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
      $user = $this->getDoctrine()->getRepository(User::Class)->find($id);
      $form = $this->createFormBuilder($user) 
      ->add('name', TextType::class, ['attr' =>['class' => 'form-control']])
      ->add('save', SubmitType::class, ['label' => 'Edit', 'attr' =>['class' => 'btn btn-primary mt-3']])
      ->getForm();
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()) {
        //$data = $form->getData();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $this->addFlash('success', 'User was succesfully edited');

        return $this->redirectToRoute('user_list');
      }
      return $this->render('users/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/user/{id}", name="show_user")
     */
    public function show($id) {
      $user = $this->getDoctrine()->getRepository(User::Class)->find($id);
        return $this->render('users/show.html.twig',['user' => $user]);
      }

    /**
     * @Route("/users/user/delete/{id}", name="delete_user")
     * @Method({"DELETE"})
     */
    public function delete($id): Response{
      $user = $this->getDoctrine()->getRepository(User::Class)->find($id);
      $name = $user->getName();
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($user);
      $entityManager->flush();

      $this->addFlash('success', 'User '.$name.' was succesfully deleted');

        return $this->redirectToRoute('user_list');
  } 
}

?>