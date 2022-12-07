<?php

namespace App\Entity;

use App\Repository\CourseClassRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourseClassRepository::class)
 */
class CourseClass
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $video;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="courseClasses")
     */
    private $course;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $previus_class;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $next_class;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getPreviusClass(): ?string
    {
        return $this->previus_class;
    }

    public function setPreviusClass(string $previus_class): self
    {
        $this->previus_class = $previus_class;

        return $this;
    }

    public function getNextClass(): ?string
    {
        return $this->next_class;
    }

    public function setNextClass(string $next_class): self
    {
        $this->next_class = $next_class;

        return $this;
    }
}
