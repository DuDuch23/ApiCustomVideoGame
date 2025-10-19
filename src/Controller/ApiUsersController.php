<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/v1/users')]
final class ApiUsersController extends AbstractController
{
    #[Route('', name: 'app_api_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function index(UserRepository $userRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        // $cacheIdentifier = "users_page-" . $page . "-" . $limit;

        // $users = $cachePool->get($cacheIdentifier,
        //     function (ItemInterface $item) use ($userRepository, $page, $limit) {
        //         $item->tag('usersCache');
        //         return $userRepository->findAllWithPagination($page, $limit);
        //     }
        // );

        $users = $userRepository->findAllWithPagination($page, $limit);

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'user_read']);
    }

    #[Route('/create', name: 'app_api_users_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function create(Request $request, SerializerInterface $serializer,
        EntityManagerInterface $em, ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher): JsonResponse
    {
        $user = $serializer->deserialize(
            $request->getContent(), 
            User::class, 
            'json', 
            ['groups' => ['user_read']]);

        $errors = $validator->validate($user);
        if($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $plainPassword = $request->toArray()['password'] ?? null;
        if($plainPassword) {
            $user->setPassword($hasher->hashPassword($user, $plainPassword));
        }

        $em->persist($user);
        $em->flush();

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'user_write']);
    }

    #[Route('/{id}', name: 'app_api_users_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user_read']);
    }


    #[Route('/edit/{id}', name: 'app_api_users_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function edit(User $currentUser, Request $request,
        SerializerInterface $serializer, EntityManagerInterface $em,
        ValidatorInterface $validator, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $editUser = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser, 'groups' => ['user_read']]
        );

        $errors = $validator->validate($editUser);
        if($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if($plainPassword = $request->toArray()['password'] ?? null) {
            $editUser->setPassword($hasher->hashPassword($editUser, $plainPassword));
        }

        $em->persist($editUser);
        $em->flush();
        return $this->json($editUser, Response::HTTP_OK, [], ['groups' => 'user_write']);
    }

    #[Route('/delete/{id}', name: 'app_api_users_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function delete(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        $cachePool->invalidateTags(['usersCache']);

        return $this->json(['message' => "Suppression de l'utilisateur avec succès."], Response::HTTP_OK);
    }
}
