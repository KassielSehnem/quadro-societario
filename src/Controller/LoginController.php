<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\ApiTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{
    private ApiTokenRepository $apiTokenRepository;

    public function __construct(ApiTokenRepository $apiTokenRepository) {
        $this->apiTokenRepository = $apiTokenRepository;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return new JsonResponse(['Credenciais incorretas.'], Response::HTTP_UNAUTHORIZED);
        }

        $apiToken = $this->apiTokenRepository->findOneBy(['user' => $user]);
        if (is_null($apiToken)) {
            $apiToken = new ApiToken();
            $apiToken->setUser($user)
                    ->setToken(bin2hex(random_bytes(8)));
            
            $this->apiTokenRepository->add($apiToken);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'token' => $apiToken->getToken()
        ]);
    }
}
