<?php

namespace App\Entity;

use App\Repository\InteractionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InteractionRepository::class)
 */
class Interaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true )
     */
    private $user_favorite;

    /**
     * @ORM\Column(type="boolean", nullable=true )
     */
    private $user_like;

    /**
     * @ORM\Column(type="boolean", nullable=true )
     */
    private $is_new;



    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="interactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="interactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $post;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserFavorite(): ?bool
    {
        return $this->user_favorite;
    }

    public function setUserFavorite(bool $user_favorite): self
    {
        $this->user_favorite = $user_favorite;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserLike()
    {
        return $this->user_like;
    }

    /**
     * @param mixed $user_like
     */
    public function setUserLike($user_like): void
    {
        $this->user_like = $user_like;
    }

    /**
     * @return mixed
     */
    public function getIsNew()
    {
        return $this->is_new;
    }

    /**
     * @param mixed $is_new
     */
    public function setIsNew($is_new): void
    {
        $this->is_new = $is_new;
    }



}
