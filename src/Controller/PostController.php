<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\SlugGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/post", name="post_")
 */
class PostController extends AbstractController
{

    public function __construct(SlugGenerator $slugGenerator){
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     */
    public function create(Request $request){

        $form = $this->createForm(PostType::class, null, [
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $post = new Post();

            $data = $form->getData();

            $post->setTitle($data->getTitle());
            $post->setIsIndexpage(false); //TODO: ENABLE SWITCHING FUNCTIONALITY
            $post->setContent($data->getContent());
            $post->setSlug($this->slugGenerator->makeSimpleSlug($data->getTitle()));

            //TODO: HANDLE IMAGES

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($post);
            $entityManager->flush();
            
            return $this->redirectToRoute('post_index');

        }

        return $this->render('post/create.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */

    public function edit($id){

        $entityManager = $this->getDoctrine()->getManager();

    }

    /**
     * @Route("/{id}/{slug}", name="show", methods={"GET"}, requirements={"id"="\d+", "slug"=".*"})
     */
    public function show($id){

        $post = $this->getDoctrine()->getManager()
            ->getRepository(Post::class)->find($id);

        return $this->render('post/show.html.twig', ['post' => $post]);

    }


    public function delete($id){

    }
}
