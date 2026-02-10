<?php

namespace App\Entity;

use App\Repository\ChapitreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapitreRepository::class)]
class Chapitre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $content = []; // Editor.js JSON data

    #[ORM\ManyToOne(inversedBy: 'chapitres')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cours $cours = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
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

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(array $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getContentAsHtml(): string
    {
        if (!$this->content || !isset($this->content['blocks'])) {
            return '';
        }

        $html = '';
        foreach ($this->content['blocks'] as $block) {
            $html .= $this->renderBlock($block);
        }

        return $html;
    }

    private function renderBlock(array $block): string
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];

        return match ($type) {
            'paragraph' => '<p>' . htmlspecialchars($data['text'] ?? '') . '</p>',
            'heading' => '<h' . (int)($data['level'] ?? 2) . '>' . htmlspecialchars($data['text'] ?? '') . '</h' . (int)($data['level'] ?? 2) . '>',
            'list' => $this->renderList($data),
            'image' => '<img src="' . htmlspecialchars($data['url'] ?? '') . '" alt="' . htmlspecialchars($data['caption'] ?? '') . '">',
            'code' => '<pre><code>' . htmlspecialchars($data['code'] ?? '') . '</code></pre>',
            default => '',
        };
    }

    private function renderList(array $data): string
    {
        $tag = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $html = '<' . $tag . '>';
        foreach ($data['items'] ?? [] as $item) {
            $html .= '<li>' . htmlspecialchars($item) . '</li>';
        }
        $html .= '</' . $tag . '>';
        return $html;
    }
}
