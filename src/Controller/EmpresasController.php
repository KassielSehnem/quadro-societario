<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\Empresa;
use App\Entity\Endereco;
use App\Entity\Pessoa;
use App\Entity\User;
use App\Repository\EmpresaRepository;
use App\Repository\PessoaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/empresas', name: 'empresas.')]
class EmpresasController extends AbstractController
{
    private EmpresaRepository $empresaRepository;
    private PessoaRepository $pessoaRepository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EmpresaRepository $empresaRepository,
        PessoaRepository $pessoaRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ) {
        $this->empresaRepository = $empresaRepository;
        $this->pessoaRepository = $pessoaRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = [
            'active' => $request->query->get('active', 'true')
        ];
        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
            $params['deleted'] = false;
        $page = (int) $request->query->get('p', 1);
        $limit = (int) $request->query->get('limit', 50);
        $offset = ($page - 1) * $limit;
        $orderBy = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        
        $empresas = $this->empresaRepository->findBy($params, [$orderBy => $direction], $limit, $offset);

        return new JsonResponse(
            $this->serializer->serialize($empresas, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['socios']]),
            Response::HTTP_OK, [], TRUE
        );
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $empresa = new Empresa();
        
        foreach ($content['endereco'] as $arrEndereco) {
            $endereco = new Endereco();
            $this->serializer->deserialize(json_encode($arrEndereco), Endereco::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $endereco]);
            $empresa->addEndereco($endereco);
        }
        unset($content['endereco']);

        $this->serializer->deserialize(json_encode($content), Empresa::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $empresa]);
        
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($empresa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            // dd($th->getMessage());
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(['message' => 'Empresa salva com o id: '. $empresa->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Empresa $empresa = null): Response
    {
        if (is_null($empresa)) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        return new JsonResponse(
            $this->serializer->serialize($empresa, 'json'),
            Response::HTTP_OK, [], true
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function update(Request $request, Empresa $empresa = null): Response
    {
        if (is_null($empresa)) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        $content = json_decode($request->getContent(), true);
        foreach ($empresa->getEnderecos() as $enderecoCadastrado) {
            $empresa->removeEndereco($enderecoCadastrado);
        }

        foreach ($content['endereco'] as $arrEndereco) {
            $endereco = new Endereco();
            $this->serializer->deserialize(json_encode($arrEndereco), Endereco::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $endereco]);
            $empresa->addEndereco($endereco);
        }
        unset($content['endereco']);

        $this->serializer->deserialize(json_encode($content), Empresa::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $empresa]);
        
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($empresa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            // dd($th->getMessage());
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            $this->serializer->serialize($empresa, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['socios']]),
            Response::HTTP_OK, [], TRUE
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Empresa $empresa = null): Response
    {
        if (is_null($empresa)) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        $empresa->setDeleted(TRUE);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($empresa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            // dd($th->getMessage());
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        
        return new JsonResponse(['message' => 'Empresa deletada com sucesso!'], Response::HTTP_OK);
    }
    
    #[Route('/{id}/socios/', name: 'socios.index', methods: ['GET'])]
    public function indexSocios(Request $request, Empresa $empresa = null): Response
    {
        if (is_null($empresa)) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        return new JsonResponse(
            $this->serializer->serialize($empresa->getSocios(), 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['empresas']]),
            Response::HTTP_OK, [], TRUE
        );
    }
    
    #[Route('/{id}/socios/', name: 'socios.new', methods: ['POST'])]
    public function createSocio(Request $request, Empresa $empresa = null): Response
    {
        if (is_null($empresa)) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        $content = $request->getContent();
        $pessoa = $this->pessoaRepository->findOneBy(['cpf' => json_decode($content, true)['cpf']]);
        if (is_null($pessoa)) {
            $pessoa = new Pessoa();
            $this->serializer->deserialize($content, Pessoa::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $pessoa]);
        }
        $pessoa->addEmpresa($empresa);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($pessoa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            // dd($th);
            return new JsonResponse(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'O Sócio foi cadastrado para a Empresa.'], Response::HTTP_CREATED);
    }

    #[Route('/{empresa_id}/socios/{pessoa_id}', name: 'socios.detail', methods: ['GET'])]
    public function detailSocio(
        #[MapEntity(id: 'empresa_id')] Empresa $empresa, 
        #[MapEntity(id: 'pessoa_id')] Pessoa $pessoa
    ): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($pessoa, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ["socios"]]),
            Response::HTTP_OK, [], TRUE
        );
    }

}
