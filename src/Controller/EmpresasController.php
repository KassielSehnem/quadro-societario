<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Endereco;
use App\Entity\Pessoa;
use App\Repository\EmpresaRepository;
use App\Repository\PessoaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Schema;

use function PHPSTORM_META\type;

#[Route('/api/empresas', name: 'empresas.')]
#[OA\Parameter(name: 'X-AUTH-TOKEN', in: 'header', required: true, description: 'O ApiToken recebido ao logar.')]
class EmpresasController extends AbstractController
{
    private EmpresaRepository $empresaRepository;
    private PessoaRepository $pessoaRepository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        EmpresaRepository $empresaRepository,
        PessoaRepository $pessoaRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->empresaRepository = $empresaRepository;
        $this->pessoaRepository = $pessoaRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna a listagem de Empresas por página, com limite padrão de 50.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Empresa::class))
        )
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Quantidade limite de Empresas por página.',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'p',
        in: 'query',
        description: 'Número da página a ser exibida, de acordo com o limite definido.',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'order',
        in: 'query',
        description: 'Parâmetro da entidade a ordernar a exibição de Empresas.',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'direction',
        in: 'query',
        description: 'Direção ordernar a exibição de Empresas. ASC ou DESC.',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Empresa', description: 'Lista de Empresas')]
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
        
        $empresas = $this->empresaRepository->findBy($params, [$orderBy => strtoupper($direction)], $limit, $offset);

        return new JsonResponse(
            $this->serializer->serialize($empresas, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['socios']]),
            Response::HTTP_OK, [], TRUE
        );
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Retorna uma mensagem de sucesso com o id da Empresa criada.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Retorna uma sequência de avisos sobre o que está errado na requisição.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Retorna uma mensagem de erro.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\RequestBody(
        content: new JsonContent(type: 'object', schema: Empresa::class),
        description: 'Um JSON com todos os campos necessários para cadastrar uma Empresa.'
    )]
    #[OA\Tag('Empresa', description: 'Criar Empresa')]
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

        $errors = $this->validator->validate($empresa);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_UNPROCESSABLE_ENTITY, [], TRUE);
        }
        
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($empresa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(['message' => 'Empresa salva com o id: '. $empresa->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna as informações detalhadas da Empresa.',
        content: new JsonContent(
            type: 'object',
            schema: Empresa::class
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa não encontrada',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Tag(name: 'Empresa', description: 'Detalhes da Empresa')]
    public function detail(Empresa $empresa = null): Response
    {
        if (is_null($empresa)) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        return new JsonResponse(
            $this->serializer->serialize($empresa, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['empresas']]),
            Response::HTTP_OK, [], true
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna a Empresa editada.',
        content: new OA\JsonContent(
            type: 'object',
            schema: Empresa::class
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa não encontrada',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Retorna uma sequência de avisos sobre o que está errado na requisição.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Retorna uma mensagem de erro.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\RequestBody(
        content: new JsonContent(type: 'object', schema: Empresa::class),
        description: 'Um JSON com todos os campos necessários para editar uma Empresa.'
    )]
    #[OA\Tag('Empresa', description: 'Editar Empresa')]
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

        $errors = $this->validator->validate($empresa);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_UNPROCESSABLE_ENTITY, [], TRUE);
        }
        
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($empresa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
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
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna uma mensagem de sucesso.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa não encontrada',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Retorna uma mensagem de erro.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Tag(name: 'Empresa', description: 'Deleta uma Empresa')]
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
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        
        return new JsonResponse(['message' => 'Empresa deletada com sucesso!'], Response::HTTP_OK);
    }
    
    #[Route('/{id}/socios/', name: 'socios.index', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna a listagem de Sócios da Empresa.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Pessoa::class))
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa não encontrada',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Tag(name: 'Sócio', description: 'Lista de sócios')]
    public function indexSocios(Request $request, Empresa $empresa = null): Response
    {
        if (is_null($empresa) || $empresa->isDeleted()) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        return new JsonResponse(
            $this->serializer->serialize($empresa->getSocios(), 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['empresas']]),
            Response::HTTP_OK, [], TRUE
        );
    }
    
    #[Route('/{id}/socios/', name: 'socios.new', methods: ['POST'])]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Retorna uma mensagem de sucesso.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa não encontrada',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Retorna uma sequência de avisos sobre o que está errado na requisição.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Retorna uma mensagem de erro.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\RequestBody(
        content: new JsonContent(type: 'object', schema: Pessoa::class),
        description: 'Um JSON com todos os campos necessários para cadastrar uma Pessoa.'
    )]
    #[OA\Tag(name: 'Sócio', description: 'Associa uma Pessoa à Empresa')]
    public function createSocio(Request $request, Empresa $empresa = null): Response
    {
        if (is_null($empresa) || $empresa->isDeleted()) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);

        $content = $request->getContent();
        $pessoa = $this->pessoaRepository->findOneBy(['cpf' => json_decode($content, true)['cpf']]);
        if (is_null($pessoa)) {
            $pessoa = new Pessoa();
            $this->serializer->deserialize($content, Pessoa::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $pessoa]);
        }
        $pessoa->addEmpresa($empresa);

        $errors = $this->validator->validate($pessoa);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_UNPROCESSABLE_ENTITY, [], TRUE);
        }

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($pessoa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            return new JsonResponse(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'O Sócio foi cadastrado para a Empresa.'], Response::HTTP_CREATED);
    }

    #[Route('/{empresa_id}/socios/{pessoa_id}', name: 'socios.detail', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna os detalhes de um Sócio.',
        content: new JsonContent(
            type: 'object',
            schema: Pessoa::class
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa ou Sócio não encontrado',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Tag(name: 'Sócio', description: 'Detalhes de um Sócio')]
    public function detailSocio(
        #[MapEntity(id: 'empresa_id')] Empresa $empresa, 
        #[MapEntity(id: 'pessoa_id')] Pessoa $pessoa
    ): Response
    {
        if (is_null($empresa) || $empresa->isDeleted()) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
        if (is_null($pessoa) || !$pessoa->getEmpresas()->contains($empresa))
            return new JsonResponse(['message' => 'Sócio não encontrado.'], Response::HTTP_NOT_FOUND);

        return new JsonResponse(
            $this->serializer->serialize($pessoa, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ["socios"]]),
            Response::HTTP_OK, [], TRUE
        );
    }

    #[Route('/{empresa_id}/socios/{pessoa_id}', name: 'socios.edit', methods: ['PUT'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna a Pessoa editada.',
        content: new OA\JsonContent(
            type: 'object',
            schema: Pessoa::class
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa ou Sócio não encontrado',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Retorna uma sequência de avisos sobre o que está errado na requisição.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Retorna uma mensagem de erro.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Tag(name: 'Sócio', description: 'Editar Pessoa')]
    public function updateSocio(
        Request $request,
        #[MapEntity(id: 'empresa_id')] Empresa $empresa,
        #[MapEntity(id: 'pessoa_id')] Pessoa $pessoa
    ): Response
    {
        if (is_null($empresa) || $empresa->isDeleted()) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
        if (is_null($pessoa) || !$pessoa->getEmpresas()->contains($empresa))
            return new JsonResponse(['message' => 'Sócio não encontrado.'], Response::HTTP_NOT_FOUND);

        $this->serializer->deserialize($request->getContent(), Pessoa::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $pessoa]);

        $errors = $this->validator->validate($pessoa);

        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_UNPROCESSABLE_ENTITY, [], TRUE);
        }

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($pessoa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            $this->serializer->serialize($pessoa, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ["socios"]]),
            Response::HTTP_OK, [], TRUE
        );
    }

    #[Route('/{empresa_id}/socios/{pessoa_id}', name: 'socios.delete', methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Retorna uma mensagem de sucesso.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Empresa ou Sócio não encontrado',
        content: new JsonContent(
            type: 'string'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Retorna uma mensagem de erro.',
        content: new OA\JsonContent(
            type: 'string'
        )
    )]
    #[OA\Tag(name: 'Sócio', description: 'Remove a associação de uma Pessoa à Empresa')]
    public function deleteSocio(
        #[MapEntity(id: 'empresa_id')] Empresa $empresa,
        #[MapEntity(id: 'pessoa_id')] Pessoa $pessoa
    ): Response
    {
        if (is_null($empresa) || $empresa->isDeleted()) 
            return new JsonResponse(['message' => 'Empresa não encontrada.'], Response::HTTP_NOT_FOUND);
        if (is_null($pessoa) || !$pessoa->getEmpresas()->contains($empresa))
            return new JsonResponse(['message' => 'Sócio não encontrado.'], Response::HTTP_NOT_FOUND);

        $pessoa->removeEmpresa($empresa);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($pessoa);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $th) {
            $this->entityManager->rollback();
            return new JsonResponse(
                ['message' => $th->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(['message' => 'Sócio removido com sucesso.'], Response::HTTP_OK);
    }

}
