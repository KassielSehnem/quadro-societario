<?php

namespace App\Entity;

use App\Repository\PessoaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PessoaRepository::class)]
class Pessoa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O nome da pessoa não pode ser vazio ou nulo.')]
    private ?string $nome = null;

    #[ORM\Column(length: 1)]
    #[Assert\EqualTo(['M', 'F'], message: 'O sexo da pessoa precisa ser M ou F.')]
    private ?string $sexo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\Date(message: 'A data de nascimento da pessoa precisa estar no formato AAAA-MM-DD.')]
    private ?\DateTimeInterface $dataNascimento = null;

    /**
     * @var Collection<int, Empresa>
     */
    #[ORM\ManyToMany(targetEntity: Empresa::class, inversedBy: 'socios')]
    #[MaxDepth(1)]
    private Collection $empresas;

    #[ORM\Column(length: 11, unique: true)]
    #[Assert\Length(exactly: 11, exactMessage: 'O CPF da pessoa precisa ter 11 dígitos, sem pontuação.')]
    private ?string $cpf = null;

    public function __construct() {
        $this->empresas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(string $sexo): static
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getDataNascimento(): ?\DateTimeInterface
    {
        return $this->dataNascimento;
    }

    public function setDataNascimento(\DateTimeInterface $dataNascimento): static
    {
        $this->dataNascimento = $dataNascimento;

        return $this;
    }

    /**
     * @return Collection<int, Empresa>
     */
    public function getEmpresas(): Collection
    {
        return $this->empresas;
    }

    public function addEmpresa(Empresa $empresa): static
    {
        if (!$this->empresas->contains($empresa)) {
            $this->empresas->add($empresa);
        }

        return $this;
    }

    public function removeEmpresa(Empresa $empresa): static
    {
        if ($this->empresas->removeElement($empresa)) {
            $empresa->removeSocio($this);
        }

        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): static
    {
        $this->cpf = $cpf;

        return $this;
    }
}
