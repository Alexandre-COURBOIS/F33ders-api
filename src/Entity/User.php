<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 *
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Cet email est déjà utilisé.",
 *     groups={"Register"},
 * )
 *
 * @UniqueEntity(
 *     fields={"username"},
 *     message="Ce nom d'utilisateur est déjà utilisé.",
 *     groups={"Register"},
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message="Merci de renseigner votre nom d'invocateur.",
     *     groups={"Register"}
     *     )
     *
     * @Assert\Length(
     *     min="2",
     *     minMessage="Merci de renseigner un nom d'invocateur correct",
     *     groups={"Register"}
     *     )
     *
     */
    private ?string $username;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message="Merci de renseigner votre email.",
     *     groups={"Register"}
     *     )
     *
     * @Assert\Email(
     *     message="Veuillez renseigner un mail valide.",
     *     groups={"Register"}
     *     )
     *
     */
    private ?string $email;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message="Merci de renseigner votre mot de passe.",
     *     groups={"Register"},
     *     )
     *
     * @Assert\Length(
     *     min="8",
     *     minMessage="Veuillez renseigner un mot de passe d'au moins 8 caractères.",
     *     groups={"Register"},
     *     )
     *
     * @Assert\Regex(
     *     pattern="/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!.:,;^%*?&µù%=&])[A-Za-z\d@$!.:,;^%*?&µù%=&]{8,}$/",
     *     message="Votre mot de passe doit contenir au moins 8 caractères, un caractère spécial, une majuscule ainsi qu'un chiffre.",
     *     groups={"Register"},
     * )
     *
     */
    private ?string $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $resetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetTokenAt;

    /**
     * @ORM\Column(type="json")
     */
    private $role = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist()
    {
        if (empty($this->getRoles())) {
            $this->setRoles(['ROLE_USER']);
        }

        if (empty($this->getToken())) {
            $this->setToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
        }

        if (empty($this->getCreatedAt())) {
            $this->setCreatedAt(new \DateTime());
        }

        if (empty($this->getIsActive())) {
            $this->setIsActive(false);
        }
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenAt(): ?\DateTimeInterface
    {
        return $this->resetTokenAt;
    }

    public function setResetTokenAt(?\DateTimeInterface $resetTokenAt): self
    {
        $this->resetTokenAt = $resetTokenAt;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->role;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->role = $roles;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt( $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }

    public function getUserpseudo(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
}
