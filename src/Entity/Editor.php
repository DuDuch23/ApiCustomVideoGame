<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EditorsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\MaxDepth;


#[ORM\Entity(repositoryClass: EditorsRepository::class)]
class Editor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(["editor_read", "editor_write", "video_game_read"])]
    #[Assert\NotBlank(message: "Le nom de l'éditeur doit être renseigné.")]
    #[Assert\Length(max: 255, maxMessage: "Le nom de l'éditeur ne doit pas dépasser 255 caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\d\s\-\',.!?()]+$/u", message: "La nom de l'éditeur contient des caractères non autorisés.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le pays de l'éditeur doit être renseigné.")]
    #[Assert\Length(max: 255, maxMessage: "Le pays de l'éditeur ne doit pas dépasser 255 caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\d\s\-\',.!?()]+$/u", message: "Le pays de l'éditeur contient des caractères non autorisés.")]
    #[Groups(["editor_read", "editor_write", "video_game_read"])]
    private ?string $country = null;

    /**
     * @var Collection<int, VideoGame>
     */
    #[ORM\OneToMany(targetEntity: VideoGame::class, mappedBy: 'editor')]
    // #[Groups(['video_game_read'])]
    // #[MaxDepth(1)]
    private Collection $videoGames;

    public function __construct()
    {
        $this->videoGames = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, VideoGame>
     */
    public function getVideoGames(): Collection
    {
        return $this->videoGames;
    }

    public function addVideoGame(VideoGame $videoGame): static
    {
        if (!$this->videoGames->contains($videoGame)) {
            $this->videoGames->add($videoGame);
            $videoGame->setEditor($this);
        }

        return $this;
    }

    public function removeVideoGame(VideoGame $videoGame): static
    {
        if ($this->videoGames->removeElement($videoGame)) {
            // set the owning side to null (unless already changed)
            if ($videoGame->getEditor() === $this) {
                $videoGame->setEditor(null);
            }
        }

        return $this;
    }
}
