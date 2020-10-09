<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/tag", name="tag_")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        return $this->render('tag/index.html.twig', [
            'controller_name' => 'TagController',
        ]);
    }

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     */

    public function create(Request $request){

        $form = $this->createForm(TagType::class, null, [
            'action' => $this->generateUrl('tag_create'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $tag = new Tag();

            $data = $form->getData();

            $tag->setName($data->getName());
            $tag->setDescription($data->getDescription());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tag);

            $entityManager->flush();

            return $this->redirectToRoute('tag_index');

        }

        return $this->render('tag/create.html.twig', ['form' => $form->createView()]);

    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit($id, Request $request){

        $entityManager = $this->getDoctrine()->getManager();

        $tag = $entityManager->getRepository(Tag::class)
            ->find($id);

        $form = $this->createForm(TagType::class, $tag, [
            'action' => $this->generateUrl('tag_create'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $data = $form->getData();

            $tag->setName($data->getName());
            $tag->setDescription($data->getDescription());

            $entityManager->persist($tag);

            $entityManager->flush();

            return $this->redirectToRoute('tag_index');

        }

        return $this->render('tag/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}/delete", name="delete", methods={"POST"})
     */

    public function delete($id){
        
    }

}
