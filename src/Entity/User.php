<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Club::class)]
    private Collection $createdClubs;

    #[ORM\ManyToMany(targetEntity: Club::class, mappedBy: 'members')]
    private Collection $joinedClubs;

    public function __construct()
    {
        $this->createdClubs = new ArrayCollection();
        $this->joinedClubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection<int, Club>
     */
    public function getCreatedClubs(): Collection
    {
        return $this->createdClubs;
    }

    public function addCreatedClub(Club $createdClub): self
    {
        if (!$this->createdClubs->contains($createdClub)) {
            $this->createdClubs->add($createdClub);
            $createdClub->setCreator($this);
        }

        return $this;
    }

    public function removeCreatedClub(Club $createdClub): self
    {
        if ($this->createdClubs->removeElement($createdClub)) {
            // set the owning side to null (unless already changed)
            if ($createdClub->getCreator() === $this) {
                $createdClub->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Club>
     */
    public function getJoinedClubs(): Collection
    {
        return $this->joinedClubs;
    }

    public function addJoinedClub(Club $joinedClub): self
    {
        if (!$this->joinedClubs->contains($joinedClub)) {
            $this->joinedClubs->add($joinedClub);
            $joinedClub->addMember($this);
        }

        return $this;
    }

    public function removeJoinedClub(Club $joinedClub): self
    {
        if ($this->joinedClubs->removeElement($joinedClub)) {
            $joinedClub->removeMember($this);
        }

        return $this;
    }
}
