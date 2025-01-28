<?php

namespace SimpleIT\ClaireExerciseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="interaction_traces")
 */
class Trace
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $interactionId;


    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $type;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $dd;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $df;
    
    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="text")
     */
    private $context;

    public function __construct(int $user_id, string $type, \DateTime $dd, \DateTime $df, string $content, string $context)
    {
        $this->userId = $user_id;
        $this->type = $type;
        $this->dd = $dd;
        $this->df = $df;
        $this->content = $content;
        $this->context = $context;
    }

    public function getInteractionId(): ?int
    {
        return $this->interactionId;
    }

    public function getUserId(): int
    {
        return $this->userId;
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

    public function getContent(): string
    {
        return $this->content;
    }
    
    public function getContext(): string
    {
        return $this->context;
    }
    
    public function __toArray(): array
    {
        return [
            'interaction_id' => $this->interactionId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'dd' => $this->dd,
            'df' => $this->df,
            'content' => $this->content,
            'context' => $this->context,
        ];
    }

    public function setUserId(int $user_id): void
    {
        $this->userId = $user_id;
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

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    public function setInteractionId(int $interaction_id): void
    {
        $this->interactionId = $interaction_id;
    }

    public function __toString(): string
    {
        return $this->type;
    }

}