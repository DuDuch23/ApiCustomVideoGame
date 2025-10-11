<?php

namespace App\Controller;

use App\Entity\Editor;
use App\Repository\EditorsRepository;
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

final class ApiEditorsController extends AbstractController
{
    #[Route('/api/v1/editors', name: 'app_api_editors', methods: ['GET'])]
    public function index(EditorsRepository $editorsRepository, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $editors = $editorsRepository->findAllWithPagination($page, $limit);
        // $videosgamesSerialized = $serializer->serialize($videogames, );

        return $this->json($editors, Response::HTTP_OK, [], ['groups' => 'editor_read']);
    }

    #[Route('/api/v1/editors/create', name:'app_api_editors_create', methods: ['POST'])]
    public function create(EditorsRepository $editorsRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $categorie = $serializer->deserialize($request->getContent(), Editor::class, 'json');
        $em->persist($categorie);
        $em->flush();

        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_editors',
            ['id' => $categorie->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Correction du contexte de sérialisation
        return $this->json($categorie, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'editor_write']);
    }

    #[Route('/api/v1/editors/edit/{id}', name:'app_api_editors_edit', methods: ['PUT'])]
    public function edit(EditorsRepository $editorsRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator, Editor $currentCategorie): JsonResponse
    {
        $editEditor = $serializer->deserialize($request->getContent(), Editor::class, 'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategorie]);
        $em->persist($editEditor);
        $em->flush();

        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_editors',
            ['id' => $editEditor->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Correction du contexte de sérialisation
        return $this->json($editEditor, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'categorie_write']);
    }

    #[Route('/api/v1/editors/delete/{id}', name:'app_api_editors_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function delete(Editor $Editor, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($Editor);
        $em->flush();

        return $this->json(['status' => 'Suppression avec succès'], Response::HTTP_OK);
    }
}
