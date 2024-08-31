<?php

namespace App\Entity;

use App\Repository\EnderecoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnderecoRepository::class)]
class Endereco
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Ignore]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank(message: 'O CEP não pode ser vazio.')]
    #[Assert\NotNull(message: 'O CEP não pode ser nulo.')]
    #[Assert\Length(exactly: 8, exactMessage: 'O CEP precisa ter 8 dígitos, sem pontuação.')]
    private ?string $cep = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O logradouro não pode ser vazio.')]
    #[Assert\NotNull(message: 'O logradouro não pode ser nulo.')]
    private ?string $logradouro = null;

    #[ORM\Column(length: 255)]
    private ?string $numero = 'S/N';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $complemento = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'A cidade não pode ser vazio.')]
    #[Assert\NotNull(message: 'A cidade não pode ser nulo.')]
    private ?string $cidade = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank(message: 'A UF não pode ser vazio.')]
    #[Assert\NotNull(message: 'A UF não pode ser nulo.')]
    #[Assert\Length(exactly: 2, exactMessage: 'A UF é composta por 2 dígitos.')]
    private ?string $uf = null;

    #[ORM\ManyToOne(inversedBy: 'enderecos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Empresa $empresa = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function setCep(string $cep): static
    {
        $this->cep = $cep;

        return $this;
    }

    public function getLogradouro(): ?string
    {
        return $this->logradouro;
    }

    public function setLogradouro(string $logradouro): static
    {
        $this->logradouro = $logradouro;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getComplemento(): ?string
    {
        return $this->complemento;
    }

    public function setComplemento(?string $complemento): static
    {
        $this->complemento = $complemento;

        return $this;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function setCidade(string $cidade): static
    {
        $this->cidade = $cidade;

        return $this;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function setUf(string $uf): static
    {
        $this->uf = $uf;

        return $this;
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): static
    {
        $this->empresa = $empresa;

        return $this;
    }
}
