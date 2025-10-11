<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\VideoGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiVideoGameController extends AbstractController
{
    #[Route('/api/v1/videogame', name: 'app_api_video_game', methods: ['GET'])]
    public function index(VideoGameRepository $videogameRepository, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $videogames = $videogameRepository->findAllWithPagination($page, $limit);
        // $videosgamesSerialized = $serializer->serialize($videogames, );

        return $this->json($videogames, Response::HTTP_OK, [], ['groups' => 'video_game_read']);
    }

    #[Route('/api/v1/videogame/create', name:'app_api_video_game_create', methods: ['POST'])]
    public function create(VideoGameRepository $videoGameRepository, Request $request
        , SerializerInterface $serializer, EntityManagerInterface $em
        , UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $videogame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json');
        $em->persist($videogame);
        $em->flush();

        $location = $urlGenerator->generate(
            'videogame',
            ['id' => $videogame->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );


        return $this->json($videogame, Response::HTTP_CREATED, ["Location" => $location], context: ["groups" => "video_game_write"]);
    }
}
