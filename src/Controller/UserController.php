<?php
namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/", name="main")
     * @Method({"GET"})
     */
    public function main()
    {

        return $this->render('main.html.twig');
    }
    /**
     * @Route("/users", name="user_list")
     * @Method({"GET"})
     */
    public function index()
    {

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('users/index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/users/user/new", name="new_user")
     * Method({"GET", "POST"})
     */
    public function newUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();
        $groups = $this->getDoctrine()->getRepository(Group::class)->findAll();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('password', PasswordType::class, ['attr' => ['class' => 'form-control']])
            ->add('email', EmailType::class, ['label' => 'Email', 'attr' => ['class' => 'form-control']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoded = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();

            $requestAll = $request->request->all();
            if (isset($requestAll['groups']['group'])) {

                $groupList = $requestAll['groups']['group'];

                foreach ($groupList as $id) {

                    $group = $this->getDoctrine()->getRepository(Group::class)->find($id);
                    if ($group->getAdmin()) {
                        $roles = $user->getRoles();
                        $roles[] = 'ROLE_ADMIN';
                        $user->setRoles($roles);
                    }
                    $newLink = new userGroup($user, $group);
                    $entityManager->persist($newLink);
                }
            }
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('info', 'User was succesfully created');

            return $this->redirectToRoute('user_list');
        }
        return $this->render('users/create.html.twig', ['form' => $form->createView(), 'groups' => $groups]);
    }

    /**
     * @Route("/users/user/edit/{id}", name="edit_user")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, int $id, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $groups = $this->getDoctrine()->getRepository(Group::class)->findAll();
        $groupList = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['user' => $user]);

        $leftGroups = User::notUserGroups($groups, $groupList);

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $requestAll = $request->request->all();

            if ($requestAll['password'] != '' && isset($requestAll['password'])) {

                $encoded = $passwordEncoder->encodePassword($user, $requestAll['password']);
                $user->setPassword($encoded);
            }
            if (isset($requestAll['groups']['group'])) {
                $groupList = $requestAll['groups']['group'];
                foreach ($groupList as $id) {
                    $group = $this->getDoctrine()->getRepository(Group::class)->find($id);
                    if ($group->getAdmin()) {
                        $roles = $user->getRoles();
                        $roles[] = 'ROLE_ADMIN';
                        $user->setRoles($roles);
                    }
                    $newLink = new userGroup($user, $group);
                    $entityManager->persist($newLink);
                }
            }
            $entityManager->flush();

            $this->addFlash('info', 'User was succesfully edited');

            return $this->redirectToRoute('user_list');
        }
        return $this->render('users/edit.html.twig', ['form' => $form->createView(), 'groups' => $leftGroups, 'list' => $groupList]);
    }

    /**
     * @Route("/users/user/{id}", name="show_user")
     */
    public function show($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $groupList = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['user' => $user]);
        return $this->render('users/show.html.twig', ['user' => $user, 'list' => $groupList]);
    }

    /**
     * @Route("/users/user/del/{id}", name="delete_user")
     * @Method({"DELETE"})
     */
    public function del($id, TokenStorageInterface $tokenStorage, SessionInterface $session)
    {
        $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

        if (!isset($id)) {
            return $this->redirectToRoute('user_list');
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $name = $user->getName();
        $entityManager = $this->getDoctrine()->getManager();

        $hasRelationship = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['user' => $user]);
        if (count($hasRelationship) > 0) {
            foreach ($hasRelationship as $relationship) {
                $entityManager->remove($relationship);
            }
        }
        if ($id == $userId) {
            $tokenStorage->setToken(null);
            $session->invalidate();
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->redirectToRoute('login');
        }
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('info', 'User ' . $name . ' was succesfully deleted');

        return $this->redirectToRoute('user_list');
    }
    /**
     * @Route("/users/user/deleteGroup/{id}", name="deleteG")
     * @Method({"DELETE"})
     */
    public function deleteGroup($id)
    {
        if (isset($id)) {
            $userGroup = $this->getDoctrine()->getRepository(UserGroup::class)->find($id);
            $user = $userGroup->getUser()->getId();

            if ($userGroup->getGrupe()->getAdmin()) {
                $relationship = $this->getDoctrine()->getRepository(UserGroup::class)->findBy(['user' => $user]);
                User::adminGroupCheck($relationship, $userGroup);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userGroup);
            $entityManager->flush();

            $this->addFlash('info', 'Group was succesfully removed');
            return $this->redirectToRoute('edit_user', ['id' => $user]);
        } else {
            $this->addFlash('info', 'Something went wrong...');
            return $this->redirectToRoute('edit_user', ['id' => $user]);
        }
    }

}
