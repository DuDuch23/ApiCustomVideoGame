<?php

namespace App\Entity;

use App\Repository\VideoGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\MaxDepth;



#[ORM\Entity(repositoryClass: VideoGameRepository::class)]
class VideoGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categorie_read', 'editor_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['video_game_read', 'video_game_write', 'categorie_read', 'editor_read'])]
    #[Assert\NotBlank(message: 'Le titre du jeu vidéo doit être renseigné.')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le titre est trop court.', maxMessage: 'Le titre est trop long.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Context(context: [DateTimeNormalizer::FORMAT_KEY => "Y-m-d"])]
    #[Groups(['video_game_read', 'video_game_write'])]
    #[Assert\NotBlank(message: 'La date de sortie du jeu vidéo doit être renseigné.')]
    // #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTime $releaseDate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['video_game_read', 'video_game_write', 'editor_read'])]
    #[Assert\NotBlank(message: 'La description du jeu vidéo doit être renseigné.')]
    #[Assert\Length(max: 2000, maxMessage: "La description ne doit pas dépasser 2000 caractères.")]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'videoGames')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['video_game_read', 'video_game_write'])]
    #[Assert\Type(type: Editor::class, message: "L'éditeur fourni est invalide.")]
    // #[MaxDepth(1)]
    private ?Editor $editor = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'videoGames')]
    #[Groups(['video_game_read', 'video_game_write'])]
    // #[MaxDepth(1)]
    private Collection $category;

    // #[ORM\Column(length: 255, nullable: true)]
    // #[Groups(['video_game_read', 'video_game_write'])]
    // private ?string $coverImage = null;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->editor;
    }

    public function setEditor(?Editor $editor): static
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }

    // public function getCoverImage(): ?string
    // {
    //     return $this->coverImage;
    // }

    // public function setCoverImage(?string $coverImage): static
    // {
    //     $this->coverImage = $coverImage;
    //     return $this;
    // }
}
