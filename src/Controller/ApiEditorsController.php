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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/editors')]
final class ApiEditorsController extends AbstractController
{
    #[Route('', name: 'app_api_editors', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "Vous n'êtes pas autorisé à modifier cet élément.")]
    public function index(EditorsRepository $editorsRepository, Request $request,
        TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        // $cacheIdentifier = "app_api_editors-" . $page . "-" . $limit;

        // $editors = $cachePool->get($cacheIdentifier, 
        //     function(ItemInterface $item) use ($editorsRepository, $page, $limit) {
        //         $item->tag("editorsCache");
        //         return $editorsRepository->findAllWithPagination($page, $limit);
        //     }
        // );

        $editors = $editorsRepository->findAllWithPagination($page, $limit);


        return $this->json($editors, Response::HTTP_OK, [], ['groups' => 'editor_read']);
    }

    #[Route('/create', name:'app_api_editors_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function create(EditorsRepository $editorsRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $editor = $serializer->deserialize($request->getContent(), Editor::class, 'json');
        
        // Correction du nom de la route pour la génération d'URL
        $location = $urlGenerator->generate(
            'app_api_editors_create',
            ['id' => $editor->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $errors = $validator->validate($editor);
        if($errors->count() > 0){
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        
        $em->persist($editor);
        $em->flush();
        return $this->json($editor, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'editor_write']);
    }

    #[Route('/edit/{id}', name:'app_api_editors_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function edit(EditorsRepository $editorsRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator, Editor $currentEditor
        , ValidatorInterface $validator): JsonResponse
    {
        $editEditor = $serializer->deserialize($request->getContent(), Editor::class, 'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEditor]);
        
        $location = $urlGenerator->generate(
            'app_api_editors_edit',
            ['id' => $editEditor->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $errors = $validator->validate($editEditor);
        if($errors->count() > 0){
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        
        $em->persist($editEditor);
        $em->flush();
        return $this->json($editEditor, Response::HTTP_CREATED, ["Location" => $location], ['groups' => 'editor_write']);
    }

    #[Route('/delete/{id}', name:'app_api_editors_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function delete(Editor $editor, EntityManagerInterface $em
        , ValidatorInterface $validator): JsonResponse
    {
        
        // $errors = $validator->validate($editEditor);
        
        // if($errors->count() > 0){
            //     return $this->json($errors, Response::HTTP_BAD_REQUEST);
            // }
            
        $em->remove($editor);
        $em->flush();
        return $this->json(['status' => 'Suppression avec succès'], Response::HTTP_OK);
    }
}
