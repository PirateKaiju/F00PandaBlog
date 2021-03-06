<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\ParsedownParser;
use App\Service\SlugGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


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
     * @Route("/all/{page}", name="paged", methods={"GET"}, requirements={"page"="\d+"})
     */
    public function getPage($page = 1){

        $entityManager = $this->getDoctrine()->getManager();

        $POSTS_PER_PAGE = 10;

        $posts = $entityManager->getRepository(Post::class)
            ->getPostsPagedSimple($page);

        //dd($posts);

        return $this->render('post/showall.html.twig', ['posts' => $posts, 'page' => $page]);

    }

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
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

            //TODO: HANDLE TAGS

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($post);
            $entityManager->flush();
            
            return $this->redirectToRoute('post_index');

        }

        return $this->render('post/create.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_USER")
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

    //@IsGranted("ROLE_USER")
    public function delete($id){

        try{
            $entityManager = $this->getDoctrine()->getManager();

            $post = $entityManager->getRepository(Post::class)->find($id);
            
            $entityManager->delete($post);

            $entityManager->flush();
        }catch(\Exception $e){
            //TODO: DO SOMETHING WITH EXCEPTION
            
        }

        return $this->redirectToRoute('user_index');
        
    }
}
