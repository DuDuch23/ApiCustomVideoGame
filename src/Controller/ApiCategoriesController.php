<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/categories')]
final class ApiCategoriesController extends AbstractController
{
    #[Route('', name: 'app_api_categories', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "Vous n'êtes pas autorisé à modifier cet élément.")]
    public function index(CategoryRepository $categoryRepository, Request $request,
        TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // $cacheIdentifier = "app_api_categories-" . $page . "-" . $limit;

        // $categories = $cachePool->get($cacheIdentifier,
        //     function(ItemInterface $item) use ($categoryRepository, $page, $limit) {
        //         $item->tag("categoriesCache");
        //         return $categoryRepository->findAllWithPagination($page, $limit);
        //     }
        // );

        $categories = $categoryRepository->findAllWithPagination($page, $limit);

        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'categorie_read']);
    }

    #[Route('/create', name:'app_api_categories_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function create(CategoryRepository $categoryRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $categorie = $serializer->deserialize($request->getContent(), Category::class, 'json');
        
        $errors = $validator->validate($categorie);
        if($errors->count() > 0){
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($categorie);
        $em->flush();

        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_categories',
            ['id' => $categorie->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json($categorie, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'categorie_write']);
    }

    #[Route('/{id}', name: 'app_api_categorie_show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => 'categorie_read']);
    }

    #[Route('/edit/{id}', name:'app_api_categories_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function edit(CategoryRepository $categoryRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator, Category $currentCategorie
        , ValidatorInterface $validator): JsonResponse
    {
        $editCategorie = $serializer->deserialize(
            $request->getContent(),
            Category::class, 
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategorie]
        );

        $errors = $validator->validate($editCategorie);
        if($errors->count() > 0){
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($editCategorie);
        
        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_categories',
            ['id' => $editCategorie->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $em->flush();
        return $this->json($editCategorie, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'categorie_write']);
    }

    #[Route('/delete/{id}', name:'app_api_categories_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function delete(Category $category, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $em->remove($category);
        $em->flush();
        $cachePool->invalidateTags(['categoriesCache']);

        return $this->json(['status' => "Suppression de la catégorie avec succès."], Response::HTTP_OK);
    }
}
