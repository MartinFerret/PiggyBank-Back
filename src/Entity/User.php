<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà utilisé, veuillez vous connecter")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups ({"show_user"})
     * @Groups ({"show_pot"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups ({"show_user"})
     * @Groups ({"show_pot"})
     * @Groups ({"show_operation"})
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\Length(min = 4)
     */
    private $password;
    

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups ({"show_user"})
     * @Groups ({"show_operation"})
     * @Groups ({"show_pot"})
     * @Assert\Length(
     *  min = 2,
     *  max = 60
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups ({"show_user"})
     * @Groups ({"show_operation"})
     * @Groups ({"show_pot"})
     * @Assert\Length(
     * min = 2,
     * max = 60)
     */
    private $lastname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $birthDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups ({"show_user"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups ({"show_user"})
     * @Assert\Iban()
     */
    private $iban;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups ({"show_user"})
     * @Groups ({"show_operation"})
     */
    private $bic;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Groups ({"show_user"})
     * @Groups ({"show_operation"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups ({"show_user"})
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Pot::class, mappedBy="user")
     * @Groups ({"show_user"})
     */
    private $pots;

    /**
     * @ORM\OneToMany(targetEntity=Operation::class, mappedBy="user")
     */
    private $operations;

    /**
     * Lorsque qu'un user est créé, il aura le ROLE_USER par défaut, son statut sera à 1 (actif) et la date du jour sera associée à la propriété ceatedAt
     */
    public function __construct ()
    {
        $this->roles[] = 'ROLE_USER';
        $this->createdAt = new \DateTime();
        $this->status = 1;
        $this->pots = new ArrayCollection();
        $this->operations = new ArrayCollection();
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
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
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Pot>
     */
    public function getPots(): Collection
    {
        return $this->pots;
    }

    public function addPot(Pot $pot): self
    {
        if (!$this->pots->contains($pot)) {
            $this->pots[] = $pot;
            $pot->setUser($this);
        }

        return $this;
    }

    public function removePot(Pot $pot): self
    {
        if ($this->pots->removeElement($pot)) {
            // set the owning side to null (unless already changed)
            if ($pot->getUser() === $this) {
                $pot->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Operation>
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $this->operations[] = $operation;
            $operation->setUser($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->removeElement($operation)) {
            // set the owning side to null (unless already changed)
            if ($operation->getUser() === $this) {
                $operation->setUser(null);
            }
        }

        return $this;
    }

    // Retourne le nom et le prénom en chaîne de caractères
    public function __toString()
    {
        return $this->getFirstname().' '. $this->getLastname();
    }

}
