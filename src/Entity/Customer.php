<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank(
        message: 'The first name cannot be blank.',
    )]
    #[Assert\Length(min: 2, max: 60)]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\Type(
        type: 'string',
        message: 'The value {{ value }} is not a valid {{ type }}.',
    )]
    #[Assert\Regex(
        pattern: '/\d/',
        match: false,
        message: 'Your first name cannot contain a number',
    )]
    #[Groups(['getCustomer', 'getClient'])]
    private string $firstName;

    #[ORM\Column(length: 60)]
    #[Assert\NotBlank(
        message: 'The last name cannot be blank.',
    )]
    #[Assert\Length(min: 2, max: 60)]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\Type(
        type: 'string',
        message: 'The value {{ value }} is not a valid {{ type }}.',
    )]
    #[Assert\Regex(
        pattern: '/\d/',
        match: false,
        message: 'Your last name cannot contain a number',
    )]
    #[Groups(['getCustomer', 'getClient'])]
    private string $lastName;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'The email cannot be blank.',
    )]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[Assert\Length(max: 255)]
    #[Groups(['getCustomer', 'getClient'])]
    private string $email;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
