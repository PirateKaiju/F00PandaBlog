<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\Post;
/**
 * @Route("/user", name="user_")
 * @IsGranted("ROLE_USER")
 */

class UserController extends AbstractController
{

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function register(Request $request){
        //only other users may create (leave this?)
        $form = $this->createForm(UserType::class, null, [
            'action' => $this->generateUrl('user_register'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $user = new User();

            $data = $form->getData();

            $user->setEmail($data->getEmail());
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $data->getPassword(),
            ));
            //$user->setRoles(["ROLE_ADMIN"]); //TODO: ENABLE ROLE SYSTEMATICALLY

            //TODO: ADD NAME TO USER?

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            $entityManager->flush();

            return $this->redirectToRoute("app_login");

        }

        return $this->render('user/register.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */

    public function edit($id, Request $request){

        $entityManager = $this->getDoctrine()->getManager();

        $user = $entityManager->getRepository(User::class)
            ->find($id);

        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('user_edit', ['id' => $id]), //TODO: CHECK IF VALID?
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $user = new User();

            $data = $form->getData();

            $user->setEmail($data->getEmail());
            $user->setPassword($this->passwordEncoder()->encodePassword(
                $user,
                $data->getPassword(),
            ));

            $entityManager->persist($user);

            $entityManager->flush();

            return $this->redirectToRoute("user_index");

        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/dashboard", name="dashboard", methods={"GET"})
     */
    public function dashboard(){
        $entityManager = $this->getDoctrine()->getManager();

        $posts = $entityManager->getRepository(Post::class)
            ->findAll(); //TODO: CONSIDER PAGINATION?
        
        return $this->render('user/dashboard.html.twig', ['posts' => $posts]);
    }
}
