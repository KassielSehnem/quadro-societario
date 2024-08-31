<?php

namespace App\Entity;

use App\Repository\EmpresaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmpresaRepository::class)]
class Empresa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O nome da Empresa não pode ser nulo ou vazio.', allowNull: false)]
    private ?string $nome = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O nome fantasia da Empresa não pode ser nulo ou vazio.', allowNull: false)]
    private ?string $nomeFantasia = null;

    #[ORM\Column(length: 14, unique: true)]
    #[Assert\Length(exactly: 14, exactMessage: 'O CNPJ da Empresa deve ter 14 dígitos, sem pontos.')]
    private ?string $cnpj = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'A razão social da Empresa não pode ser nulo ou vazio.', allowNull: false)]
    private ?string $razaoSocial = null;

    /**
     * @var Collection<int, Endereco>
     */
    #[ORM\OneToMany(targetEntity: Endereco::class, mappedBy: 'empresa', orphanRemoval: true, cascade: ['persist'])]
    private Collection $enderecos;

    /**
     * @var Collection<int, Pessoa>
     */
    #[ORM\ManyToMany(targetEntity: Pessoa::class, mappedBy: 'empresas')]
    private Collection $socios;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private bool $deleted = false;

    public function __construct()
    {
        $this->enderecos = new ArrayCollection();
        $this->socios = new ArrayCollection();
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

    public function getNomeFantasia(): ?string
    {
        return $this->nomeFantasia;
    }

    public function setNomeFantasia(string $nomeFantasia): static
    {
        $this->nomeFantasia = $nomeFantasia;

        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): static
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    public function getRazaoSocial(): ?string
    {
        return $this->razaoSocial;
    }

    public function setRazaoSocial(string $razaoSocial): static
    {
        $this->razaoSocial = $razaoSocial;

        return $this;
    }

    /**
     * @return Collection<int, Endereco>
     */
    public function getEnderecos(): Collection
    {
        return $this->enderecos;
    }

    public function addEndereco(Endereco $endereco): static
    {
        if (!$this->enderecos->contains($endereco)) {
            $this->enderecos->add($endereco);
            $endereco->setEmpresa($this);
        }

        return $this;
    }

    public function removeEndereco(Endereco $endereco): static
    {
        if ($this->enderecos->removeElement($endereco)) {
            // set the owning side to null (unless already changed)
            if ($endereco->getEmpresa() === $this) {
                $endereco->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Pessoa>
     */
    public function getSocios(): Collection
    {
        return $this->socios;
    }

    public function addSocio(Pessoa $socio): static
    {
        if (!$this->socios->contains($socio)) {
            $this->socios->add($socio);
            $socio->addEmpresa($this);
        }

        return $this;
    }

    public function removeSocio(Pessoa $socio): static
    {
        if ($this->socios->removeElement($socio)) {
            $socio->removeEmpresa($this);
        }

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
    
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }
}
