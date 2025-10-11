<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\VideoGame;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class ApiCategoriesController extends AbstractController
{
    #[Route('/api/v1/categories', name: 'app_api_categories', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $categories = $categoryRepository->findAllWithPagination($page, $limit);
        // $videosgamesSerialized = $serializer->serialize($videogames, );

        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'categorie_read']);
    }

    #[Route('/api/v1/categories/create', name:'app_api_categories_create', methods: ['POST'])]
    public function create(CategoryRepository $categoryRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $categorie = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $em->persist($categorie);
        $em->flush();

        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_categories',
            ['id' => $categorie->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Correction du contexte de sérialisation
        return $this->json($categorie, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'categorie_write']);
    }

    #[Route('/api/v1/categories/edit/{id}', name:'app_api_categories_edit', methods: ['PUT'])]
    public function edit(CategoryRepository $categoryRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator, Category $currentCategorie): JsonResponse
    {
        $editCategorie = $serializer->deserialize($request->getContent(), Category::class, 'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategorie]);
        $em->persist($editCategorie);
        $em->flush();

        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_categories',
            ['id' => $editCategorie->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Correction du contexte de sérialisation
        return $this->json($editCategorie, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'categorie_write']);
    }

    #[Route('/api/v1/categories/delete/{id}', name:'app_api_categories_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function delete(Category $category, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();

        return $this->json(['status' => 'Suppression avec succès'], Response::HTTP_OK);
    }
}
