<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['list_posts','get_post','get_category'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: 'le titre est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 60,
        minMessage: 'Le titre est trop court ( {{ limit }} caracteres minimum)',
        maxMessage: 'Le titre est trop long ( {{ limit }} caracteres maximum)',
    )]
    #[Groups(['list_posts','get_post','get_category'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotNull(message: 'le contenue est obligatoire')]
    #[Assert\Length(
        min: 20,
        minMessage: 'Le contenue est trop court ( {{ limit }} caracteres minimum)',
    )]
    #[Groups(['list_posts','get_post'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['get_post'])]
    private ?\DateTime $CreatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['list_posts','get_post','get_category'])]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTime $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
