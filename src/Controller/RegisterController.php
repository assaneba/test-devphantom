<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_register', methods:["POST"])]
    public function register(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, NormalizerInterface $normalizer, UserPasswordHasherInterface $passwordHasher)
    {
        $jsonData = $request->getContent();
        $user = $serializer->deserialize($jsonData, User::class, 'json');

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();
            
        $data = $normalizer->normalize($user);

        return $this->json("Utilisateur créé !", Response::HTTP_CREATED);
    }
}
