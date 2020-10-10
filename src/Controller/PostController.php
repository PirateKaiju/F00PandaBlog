<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\ParsedownParser;
use App\Service\SlugGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/post", name="post_")
 */
class PostController extends AbstractController
{

    public function __construct(SlugGenerator $slugGenerator, ParsedownParser $parser){
        $this->slugGenerator = $slugGenerator;
        $this->parseDownParser = $parser;
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
            $post->setPublishedDate($data->getPublishedDate());

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
    public function edit($id, Request $request){

        $entityManager = $this->getDoctrine()->getManager();

        $post = $entityManager->getRepository(Post::class)
            ->find($id);

        $form = $this->createForm(PostType::class, $post, [
            'action' => $this->generateUrl('post_edit', ["id" => $id]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $data = $form->getData();

            $post->setTitle($data->getTitle());
            $post->setContent($data->getContent());
            $post->setSlug($this->slugGenerator->makeSimpleSlug($data->getTitle()));
            $post->setPublishedDate($data->getPublishedDate());

            $entityManager->persist($post);

            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/edit.html.twig', ['form' => $form->createView()]);


    }

    /**
     * @Route("/{id}/{slug}", name="show", methods={"GET"}, requirements={"id"="\d+", "slug"=".*"})
     */
    public function show($id, $slug){

        $post = $this->getDoctrine()->getManager()
            ->getRepository(Post::class)->find($id);

        //dd($this->parseDownParser->markdownToHtml($post->getContent()));
        //TODO: CHECK SLUGS

        $htmlContent = $this->parseDownParser->markdownToHtml($post->getContent());

        return $this->render('post/show.html.twig', ['post' => $post, 'htmlContent' => $htmlContent]);

    }


    public function delete($id){

    }
}
