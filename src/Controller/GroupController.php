<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class GroupController extends AbstractController
{
    /**
     * @Route("/groups", name="group_list")
     * @Method({"GET"})
     */
    public function index() {

        $groups = $this->getDoctrine()->getRepository(Group::Class)->findAll();
        return $this->render('groups/index.html.twig',['groups' => $groups]);
      }

      /**
     * @Route("/groups/group/new", name="new_group")
     * Method({"GET", "POST"})
     */
    public function newGroup(Request $request) {

        $group = new Group();
        $users = $this->getDoctrine()->getRepository(User::Class)->findAll();

        $form = $this->createFormBuilder($group) 
        ->add('title', TextType::class, ['attr' =>['class' => 'form-control']])
        ->add('save', SubmitType::class, ['label' => 'Create', 'attr' =>['class' => 'btn btn-primary mt-3']])
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
          $data = $form->getData();
  
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($group);
          $entityManager->flush();
  
          $this->addFlash('success', 'Group was succesfully created');
  
          return $this->redirectToRoute('group_list');
        }
        return $this->render('groups/create.html.twig', ['form' => $form->createView(), 'users' => $users]);
      }
  
      /**
       * @Route("/groups/group/edit/{id}", name="edit_group")
       * Method({"GET", "POST"})
       */
      public function edit(Request $request, $id) {

        $group = $this->getDoctrine()->getRepository(Group::Class)->find($id);
        $form = $this->createFormBuilder($group) 
        ->add('title', TextType::class, ['attr' =>['class' => 'form-control']])
        ->add('save', SubmitType::class, ['label' => 'Edit', 'attr' =>['class' => 'btn btn-primary mt-3']])
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
          //$data = $form->getData();
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();
  
          $this->addFlash('success', 'Group was succesfully edited');
  
          return $this->redirectToRoute('group_list');
        }
        return $this->render('groups/edit.html.twig', ['form' => $form->createView()]);
      }

      /**
     * @Route("/groups/group/{id}", name="show_group")
     */
    public function show($id) {
        $group = $this->getDoctrine()->getRepository(Group::Class)->find($id);
        $userList = $this->getDoctrine()->getRepository(UserGroup::Class)->findBy(['grupe' => $group]);
          return $this->render('groups/show.html.twig',['group' => $group, 'list' => $userList]);
        }
  
      /**
       * @Route("/groups/group/delete/{id}", name="delete_group")
       * @Method({"DELETE"})
       */
      public function delete($id): Response{
        $group = $this->getDoctrine()->getRepository(Group::Class)->find($id);
        $title = $group->getTitle();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($group);
        $entityManager->flush();
  
        $this->addFlash('success', 'Group '.$title.' was succesfully deleted');
  
          return $this->redirectToRoute('group_list');
    } 
}
