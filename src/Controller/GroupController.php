<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    /**
     * @Route("/groups", name="group_list")
     * @Method({"GET"})
     */
    public function index()
    {

        $groups = $this->getDoctrine()->getRepository(Group::class)->findAll();
        return $this->render('groups/index.html.twig', ['groups' => $groups]);
    }

    /**
     * @Route("/groups/group/new", name="new_group")
     * Method({"GET", "POST"})
     */
    public function newGroup(Request $request)
    {

        $group = new Group();
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        $form = $this->createFormBuilder($group)
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('admin', ChoiceType::class, ['attr' => ['class' => 'form-control'],
                'choices' => [
                    'No' => false,
                    'Yes' => true,
                ]])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            
            $requestAll = $request->request->all();
            if (isset($requestAll['users']['user'])) {

                $userList = $requestAll['users']['user'];

                foreach ($userList as $id) {

                    $user = $this->getDoctrine()->getRepository(User::class)->find($id);
                    $newLink = new userGroup($user, $group);
                    $entityManager->persist($newLink);
                }
            }
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('info', 'Group was succesfully created');

            return $this->redirectToRoute('group_list');
        }
        return $this->render('groups/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/groups/group/edit/{id}", name="edit_group")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id)
    {

        $group = $this->getDoctrine()->getRepository(Group::class)->find($id);
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $userList = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['grupe' => $group]);

        $usersNotInGroup = Group::usersNotInGroup($users, $userList);

        $form = $this->createFormBuilder($group)
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $requestAll = $request->request->all();

            //$user = $this->getDoctrine()->getRepository(User::class)->find($request->request->get('user_id'));
            if (isset($requestAll['users']['user'])) {
                $userList = $requestAll['users']['user'];
                foreach ($userList as $id) {
                    $user = $this->getDoctrine()->getRepository(User::class)->find($id);
                    $newLink = new userGroup($user, $group);
                    $entityManager->persist($newLink);
                }
            }
            $entityManager->flush();

            $this->addFlash('info', 'Group was succesfully edited');

            return $this->redirectToRoute('group_list');
        }
        return $this->render('groups/edit.html.twig',
            ['form' => $form->createView(), 'users' => $usersNotInGroup, 'list' => $userList]);
    }

    /**
     * @Route("/groups/group/{id}", name="show_group")
     */
    public function show($id)
    {
        $group = $this->getDoctrine()->getRepository(Group::class)->find($id);
        $userList = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['grupe' => $group]);
        return $this->render('groups/show.html.twig', ['group' => $group, 'list' => $userList]);
    }

    /**
     * @Route("/groups/group/delete/{id}", name="delete_group")
     * @Method({"DELETE"})
     */
    public function delete($id): Response
    {
        if(!isset($id)) {
            return $this->redirectToRoute('group_list');
        }
        $group = $this->getDoctrine()->getRepository(Group::class)->find($id);
        $title = $group->getTitle();
        $hasRelationship = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['grupe' => $group]);

        if(count($hasRelationship) == 0 && isset($hasRelationship)){
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($group);
        $entityManager->flush();

        $this->addFlash('info', 'Group ' . $title . ' was succesfully deleted');

        return $this->redirectToRoute('group_list');
        } else {
            $this->addFlash('info', 'Group ' . $title . ' cannot be deleted - still have members');

            return $this->redirectToRoute('group_list');
        }
    }
    /**
     * @Route("/groups/group/deleteUser/{id}", name="deleteU")
     * @Method({"DELETE"})
     */
    public function deleteUser($id): Response
    {
        $userGroup = $this->getDoctrine()->getRepository(UserGroup::class)->find($id);
        $group = $userGroup->getGrupe()->getId();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($userGroup);
        $entityManager->flush();

        $this->addFlash('info', 'User was succesfully removed');

        return $this->redirectToRoute('edit_group', ['id' => $group]);
    }
}
