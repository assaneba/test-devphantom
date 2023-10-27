<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $postRepository;
    private $normalizer;

    public function __construct(PostRepository $postRepository, NormalizerInterface $normalizer)
    {
        $this->postRepository = $postRepository;
        $this->normalizer = $normalizer;
    }
    
    #[Route('/api/posts', name: 'app_post_create', methods:["POST"])]
    public function createPost(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $jsonData = $request->getContent();
        $post = $serializer->deserialize($jsonData, Post::class, 'json');

        //$post = new Post();

        $post->setCreatedAt(new \DateTimeImmutable);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if($form->isSubmitted() AND $form->isValid()) 
            $form->getData();
            $entityManager->persist($post);
            $entityManager->flush();
            
            $data = $this->normalizer->normalize($post);

            return $this->json($data, Response::HTTP_CREATED);
        

        $errors = $this->getErrorsFromForm($form);

        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    private function getErrorsFromForm($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true, true) as $error) {
            $field = $error->getOrigin();
            $fieldName = $field->getName();
            $errors[$fieldName] = $error->getMessage();
        }

        return $errors;
    }

    #[Route('/api/posts', name: 'app_post_list', methods:["GET"])]
    public function listPost(): JsonResponse
    {
        $posts = $this->postRepository->findAll();
        
        $data = $this->normalizer->normalize($posts);
        //dd($data);

        return new JsonResponse($data);
    }

    #[Route('/api/posts/{idPost}', name: 'app_post_single', methods:["GET"])]
    public function singlePost($idPost): JsonResponse
    {
        $post = $this->postRepository->findOneById($idPost);
        
        $data = $this->normalizer->normalize($post);

        return new JsonResponse($data);
    }

}
