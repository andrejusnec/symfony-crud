<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Group;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserController extends AbstractController {
    /**
     * @Route("/", name="main")
     * @Method({"GET"})
     */
    public function main() {

      return $this->render('main.html.twig');
    }
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
      $groups = $this->getDoctrine()->getRepository(Group::Class)->findAll();

      $form = $this->createFormBuilder($user) 
      ->add('name', TextType::class, ['attr' =>['class' => 'form-control']])
      ->add('password', PasswordType::class, ['attr' =>['class' => 'form-control']])
      ->add('save', SubmitType::class, ['label' => 'Create', 'attr' =>['class' => 'btn btn-primary mt-3']])
      ->getForm();
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        $entityManager = $this->getDoctrine()->getManager();

        //$group = $this->getDoctrine()->getRepository(Group::Class)->find($request->request->get('group_id'));
        
        $requestAll = $request->request->all();
        $groupList = $requestAll['groups']['group'];
        //dump($persons);
        //die;
        if($groupList != null) {
          foreach ($groupList as $id) {
          
          $group = $this->getDoctrine()->getRepository(Group::Class)->find($id);
          $newLink = new userGroup($user, $group);
          $entityManager->persist($newLink);
          }
        }
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'User was succesfully created');

        return $this->redirectToRoute('user_list');
      }
      return $this->render('users/create.html.twig', ['form' => $form->createView(), 'groups' => $groups]);
    }

    /**
     * @Route("/users/user/edit/{id}", name="edit_user")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {

      $user = $this->getDoctrine()->getRepository(User::Class)->find($id);
      $groups = $this->getDoctrine()->getRepository(Group::Class)->findAll();

      $form = $this->createFormBuilder($user) 
      ->add('name', TextType::class, ['attr' =>['class' => 'form-control']])
      ->add('password', PasswordType::class, ['attr' =>['class' => 'form-control']])
      ->add('save', SubmitType::class, ['label' => 'Edit', 'attr' =>['class' => 'btn btn-primary mt-3']])
      ->getForm();
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()) {
        //$data = $form->getData();
        $entityManager = $this->getDoctrine()->getManager();
        $group = $this->getDoctrine()->getRepository(Group::Class)->find($request->request->get('group_id'));
        if($group != null) {
          $newLink = new userGroup($user, $group);
          $entityManager->persist($newLink);
        }
        $entityManager->flush();

        $this->addFlash('success', 'User was succesfully edited');

        return $this->redirectToRoute('user_list');
      }
      return $this->render('users/edit.html.twig', ['form' => $form->createView(), 'groups' => $groups]);
    }

    /**
     * @Route("/users/user/{id}", name="show_user")
     */
    public function show($id) {
      $user = $this->getDoctrine()->getRepository(User::Class)->find($id);
      $groupList = $this->getDoctrine()->getRepository(UserGroup::Class)->findBy(['user' => $user]);
        return $this->render('users/show.html.twig',['user' => $user, 'list' => $groupList]);
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
    /**
     * @Route("/users/user/deleteGroup/{id}", name="deleteG")
     * @Method({"DELETE"})
     */
    public function deleteGroup($id): Response{
      $userGroup = $this->getDoctrine()->getRepository(UserGroup::Class)->find($id);
      $user = $userGroup->getUser()->getId();
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($userGroup);
      $entityManager->flush();

      $this->addFlash('success', 'Group was succesfully removed');

        return $this->redirectToRoute('show_user', ['id' => $user]);
  } 

}

?>

