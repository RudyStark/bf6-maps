<?php

namespace App\Entity;

use App\Repository\MapRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MapRepository::class)]
#[ORM\Table(name: 'maps')]
#[ORM\HasLifecycleCallbacks]
class Map
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Slug unique (affichage / URL)
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private string $slug;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private string $title;

    // Code BF6 (saisi par l’auteur) - on le cache côté UI et on le révèle via un endpoint
    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $code;

    // Auteur affiché - on liera plus tard au profil (username Supabase)
    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private string $authorName;

    // URLs des screenshots (array de strings)
    #[ORM\Column(type: 'json')]
    #[Assert\All([
        new Assert\Url(protocols: ['http', 'https'])
    ])]
    private array $screenshots = [];

    // ——— Paramètres gameplay ———

    // 10 -> 60 (minutes)
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    #[Assert\Range(min: 10, max: 60)]
    private int $gameTimeMinutes = 10;

    // Rotation des maps: liste de noms (ou codes) – JSON array
    #[ORM\Column(type: 'json')]
    private array $mapRotation = [];

    // Global - % dégâts (10 -> 500), 100 par défaut
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    #[Assert\Range(min: 10, max: 500)]
    private int $globalDamageMultiplier = 100;

    // Setup - joueurs humains (1 -> 32), 32 par défaut
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    #[Assert\Range(min: 1, max: 32)]
    private int $humanPlayers = 32;

    // Soldier
    #[ORM\Column(type: 'boolean')]
    private bool $friendlyFire = false;

    #[ORM\Column(type: 'boolean')]
    private bool $squadRevive = true;

    // 1 -> 4, 4 par défaut
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    #[Assert\Range(min: 1, max: 4)]
    private int $squadSize = 4;

    // Tags choisis par l’auteur (ex: ["AIM ASSIST","HARDCORE MODE","LOCK WEAPONS","INFINITE AMMO","VEHICLE ON","BOT"])
    #[ORM\Column(type: 'json')]
    private array $tags = [];

    // Popularité = nb de fois que le code a été révélé via l’UI
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $codeRevealCount = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $slug,
        string $title,
        string $code,
        string $authorName
    ) {
        $this->slug = $slug;
        $this->title = $title;
        $this->code = $code;
        $this->authorName = $authorName;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters / setters simples

    public function getId(): ?int { return $this->id; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getAuthorName(): string { return $this->authorName; }
    public function setAuthorName(string $authorName): self { $this->authorName = $authorName; return $this; }

    /** @return string[] */
    public function getScreenshots(): array { return $this->screenshots; }
    /** @param string[] $screenshots */
    public function setScreenshots(array $screenshots): self { $this->screenshots = \array_values($screenshots); return $this; }

    public function getGameTimeMinutes(): int { return $this->gameTimeMinutes; }
    public function setGameTimeMinutes(int $m): self { $this->gameTimeMinutes = $m; return $this; }

    /** @return string[] */
    public function getMapRotation(): array { return $this->mapRotation; }
    /** @param string[] $rotation */
    public function setMapRotation(array $rotation): self { $this->mapRotation = \array_values($rotation); return $this; }

    public function getGlobalDamageMultiplier(): int { return $this->globalDamageMultiplier; }
    public function setGlobalDamageMultiplier(int $p): self { $this->globalDamageMultiplier = $p; return $this; }

    public function getHumanPlayers(): int { return $this->humanPlayers; }
    public function setHumanPlayers(int $n): self { $this->humanPlayers = $n; return $this; }

    public function isFriendlyFire(): bool { return $this->friendlyFire; }
    public function setFriendlyFire(bool $flag): self { $this->friendlyFire = $flag; return $this; }

    public function isSquadRevive(): bool { return $this->squadRevive; }
    public function setSquadRevive(bool $flag): self { $this->squadRevive = $flag; return $this; }

    public function getSquadSize(): int { return $this->squadSize; }
    public function setSquadSize(int $n): self { $this->squadSize = $n; return $this; }

    /** @return string[] */
    public function getTags(): array { return $this->tags; }
    /** @param string[] $tags */
    public function setTags(array $tags): self { $this->tags = \array_values($tags); return $this; }

    public function getCodeRevealCount(): int { return $this->codeRevealCount; }
    public function incrementCodeReveal(): void { $this->codeRevealCount++; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
