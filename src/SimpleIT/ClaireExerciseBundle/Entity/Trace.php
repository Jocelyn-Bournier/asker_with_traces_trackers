<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'interaction_traces')]

class Trace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $interaction_id = null;

    #[ORM\Column(type: 'integer')]
    private int $user_id;

    #[ORM\Column(type: 'string', length: 128)]
    private string $type;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $dd;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $df;
    
    #[ORM\Column(type: 'json')]
    private array $content;

    #[ORM\Column(type: 'json')]
    private array $context;

    public function __construct(int $user_id, string $type, \DateTime $dd, \DateTime $df, array $content, array $context)
    {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->dd = $dd;
        $this->df = $df;
        $this->content = $content;
        $this->context = $context;
    }

    public function getInteractionId(): ?int
    {
        return $this->interaction_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getType(): string
    {
        return $this->type;
    }
    
    public function getDd(): \DateTime
    {
        return $this->dd;
    }
    
    public function getDf(): \DateTime
    {
        return $this->df;
    }

    public function getContent(): array
    {
        return $this->content;
    }
    
    public function getContext(): array
    {
        return $this->context;
    }
    
    public function __toArray(): array
    {
        return [
            'interaction_id' => $this->interaction_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'dd' => $this->dd,
            'df' => $this->df,
            'content' => $this->content,
            'context' => $this->context,
        ];
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }
    
    public function setType(string $type): void
    {
        $this->type = $type;
    }
    
    public function setDd(\DateTime $dd): void
    {
        $this->dd = $dd;
    }
    
    public function setDf(\DateTime $df): void
    {
        $this->df = $df;
    }

    public function setContent(array $content): void
    {
        $this->content = $content;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function setInteractionId(int $interaction_id): void
    {
        $this->interaction_id = $interaction_id;
    }

    public function __toString(): string
    {
        return $this->type;
    }

}