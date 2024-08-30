<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmpresaController extends AbstractController
{
    #[Route('/api/empresa', name: 'app_empresa')]
    public function index(): Response
    {
        return new JsonResponse();
    }
}
