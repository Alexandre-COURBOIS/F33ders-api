<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message="Merci de renseigner votre nom.",
     *     groups={"Contact"}
     *     )
     *
     * @Assert\Length(
     *     min="2",
     *     minMessage="Merci de renseigner un nom correct",
     *     groups={"Contact"}
     *     )
     *
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message="Merci de renseigner votre prenom.",
     *     groups={"Contact"}
     *     )
     *
     * @Assert\Length(
     *     min="2",
     *     minMessage="Merci de renseigner un prenom correct",
     *     groups={"Contact"}
     *     )
     *
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(
     *     message="Merci de renseigner votre email.",
     *     groups={"Contact"}
     *     )
     *
     * @Assert\Email(
     *     message="Veuillez renseigner un email valide afin que nous puissions vous recontacter suite à votre demande",
     *     groups={"Contact"}
     *     )
     *
     * @Assert\Length(
     *     min="5",
     *     minMessage="Merci de renseigner une adresse email correct",
     *     groups={"Contact"}
     *     )
     *
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\Length(
     *     min="5",
     *     minMessage="Merci de renseigner un message correct",
     *     groups={"Contact"}
     *     )
     *
     */
    private $message;

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

        if (empty($this->getCreatedAt())) {
            $this->setCreatedAt(new \DateTime());
        }

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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
}
