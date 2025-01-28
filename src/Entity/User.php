<?php

namespace App\Entity;

use App\Enum\Rol;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'usuario',schema: 'streetrats')]
class User
#[ORM\Table(name: 'usuario',schema: 'streetrats')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (name: 'id')]
    private ?int $id = null;

    #[ORM\Column(name: 'username', length: 255)]
    private ?string $username = null;

    #[ORM\Column(name: 'password', length: 600)]
    private ?string $password = null;

    #[ORM\Column(name: 'rol', enumType: Rol::class)]
    private ?Rol $rol = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRol(): array
    {
        return  $this->rol;
    }

    public function setRol(Rol $rol): static
    {
        $this->rol = $rol;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->rol->value];
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
