<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueConstraint(name: 'email_unique', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user', 'reservation'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['user', 'reservation'])]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null; 

    #[ORM\Column(type: 'json')]
    #[Groups(['user'])]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'string')]
    #[Groups(['user', 'reservation'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['user'])]
    private ?string $phoneNumber = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Reservation::class)]
    #[Groups(['user'])]
    private Collection $reservations;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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

    public function getRoles(): array
    {
        return $this->roles ?? ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
      

    }
    
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function setReservations(Collection $reservations): static
    {
        $this->reservations = $reservations;

        return $this;
    }


}


