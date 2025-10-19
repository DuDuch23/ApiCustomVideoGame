<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Entity\Editor;
use App\Entity\Category;
use App\Repository\VideoGameRepository;
use App\Repository\EditorsRepository;
use App\Repository\CategoryRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/v1/videogames')]
final class ApiVideoGameController extends AbstractController
{
    #[Route('', name: 'app_api_video_games', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: "Vous n'êtes pas autorisé à modifier cet élément.")]
    public function index(VideoGameRepository $videogameRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        
        // $cacheIdentifier = "app_api_video_games-" . $page . "-" . $limit;

        // return null pour les editeurs et catégories après rappelle de la route une deuxième fois (à corriger)
        // $videogames = $cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($videogameRepository, $page, $limit) {
        //     $item->tag("videoGameCache");
        //     return $videogameRepository->findAllWithPagination($page, $limit);
        // });

        $videogames = $videogameRepository->findAllWithPagination($page, $limit);

        return $this->json($videogames, Response::HTTP_OK, [], [
            'groups' => ['video_game_read'],
            'enable_max_depth' => true 
        ]);
    }

    #[Route('/create', name: 'api_video_game_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à modifier cet élément.")]
    public function new(Request $request, EntityManagerInterface $em, SerializerInterface $serializer,
        EditorsRepository $editorRepository,CategoryRepository $categoryRepository,
        UrlGeneratorInterface $urlGenerator): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        $videoGame = $serializer->deserialize(
            json_encode([
                'title' => $data['title'],
                'releaseDate' => $data['releaseDate'],
                'description' => $data['description']
            ]),
            VideoGame::class,
            'json'
        );

        if (!empty($data['editor'])) {
            $editorId = basename($data['editor']);
            $editor = $editorRepository->find($editorId);
            if (!$editor) {
                return $this->json(['error' => 'Éditeur introuvable'], Response::HTTP_BAD_REQUEST);
            }
            $videoGame->setEditor($editor);
        }

        if (!empty($data['category'])) {
            $categories = is_array($data['category']) ? $data['category'] : [$data['category']];
            foreach ($categories as $catPath) {
                $catId = basename($catPath);
                $category = $categoryRepository->find($catId);
                if (!$category) {
                    return $this->json(['error' => "Catégorie $catId introuvable"], Response::HTTP_BAD_REQUEST);
                }
                $videoGame->addCategory($category);
            }
        }
        
        $coverImage = $request->files->get('coverImage');
        if($coverImage){
            $newFileName = uniqid().'.'.$coverImage->guessExtension();
            try{
                $coverImage->move(
                    $this->getParameter('covers_directory'),
                    $newFileName
                );
            }catch(FileException $e){
                return new JsonResponse(['error' => "Impossible d'enregistrer l'image"], 500);
            }
        }

        $em->persist($videoGame);
        $em->flush();

        $location = $urlGenerator->generate(
            'api_video_game_new',
            ['id' => $videoGame->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->json(
            $videoGame,
            Response::HTTP_CREATED,
            ["Location" => $location],
            ['groups' => 'video_game_read']
        );
    }


    #[Route('/edit/{id}', name: 'app_api_video_game_edit', methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à modifier cet élément.")]
    public function edit(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        VideoGame $currentVideogame,
        EditorsRepository $editorRepository,
        CategoryRepository $categoryRepository,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $editVideogame = $serializer->deserialize(
            json_encode([
                'title' => $data['title'] ?? $currentVideogame->getTitle(),
                'releaseDate' => $data['releaseDate'] ?? $currentVideogame->getReleaseDate()?->format('Y-m-d'),
                'description' => $data['description'] ?? $currentVideogame->getDescription(),
            ]),
            VideoGame::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentVideogame]
        );

        if (!empty($data['editor'])) {
            $editorId = basename($data['editor']);
            $editor = $editorRepository->find($editorId);
            if (!$editor) {
                return $this->json(['error' => 'Éditeur introuvable'], Response::HTTP_BAD_REQUEST);
            }
            $editVideogame->setEditor($editor);
        }

        if (!empty($data['category'])) {
            foreach ($currentVideogame->getCategory() as $cat) {
                $currentVideogame->removeCategory($cat);
            }

            $categories = is_array($data['category']) ? $data['category'] : [$data['category']];
            foreach ($categories as $catPath) {
                $catId = basename($catPath);
                $category = $categoryRepository->find($catId);
                if (!$category) {
                    return $this->json(['error' => "Catégorie $catId introuvable"], Response::HTTP_BAD_REQUEST);
                }
                $editVideogame->addCategory($category);
            }
        }

        // Validation
        $errors = $validator->validate($editVideogame);
        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json($editVideogame, Response::HTTP_OK, [], ['groups' => 'video_game_read']);
    }

    #[Route('/delete/{id}', name: 'app_api_video_game_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'êtes pas autorisé à supprimer cet élément.")]
    public function delete(VideoGame $videogame, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($videogame);
        $em->flush();

        return $this->json(['status' => 'Suppression avec succès'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_api_video_game_show', methods: ['GET'])]
    public function show(VideoGame $videogame): JsonResponse
    {
        return $this->json($videogame, Response::HTTP_OK, [], ['groups' => 'video_game_read']);
    }
}
