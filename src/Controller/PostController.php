<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Form\PostFormService;
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
    private $postFormService;

    public function __construct(PostRepository $postRepository, NormalizerInterface $normalizer, PostFormService $postFormService)
    {
        $this->postRepository = $postRepository;
        $this->normalizer = $normalizer;
        $this->postFormService = $postFormService;
    }
    
    #[Route('/api/posts', name: 'app_post_create', methods:["POST"])]
    public function createPost(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $post = $serializer->deserialize($request->getContent(), Post::class, 'json');

        $post->setCreatedAt(new \DateTimeImmutable);

        $form = $this->postFormService->createPostForm(PostType::class, $post);
        $dataForm = $this->postFormService->handleFormSubmission($form, $request);
        
        if($dataForm) 
            $form->getData();
            $entityManager->persist($post);
            $entityManager->flush();
            
            $data = $this->normalizer->normalize($post);

            return $this->json($data, Response::HTTP_CREATED);
    }

    #[Route('/api/posts', name: 'app_post_list', methods:["GET"])]
    public function listPost(): JsonResponse
    {
        $posts = $this->postRepository->findAll();
        
        $data = $this->normalizer->normalize($posts);

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
