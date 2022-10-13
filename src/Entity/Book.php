<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $front_page;

    /**
     * @ORM\OneToMany(targetEntity=BookEntry::class, mappedBy="book")
     */
    private $bookEntries;

    /**
     * @ORM\Column(type="text")
     */
    private $Description;

    public function __construct()
    {
        $this->bookEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFrontPage(): ?string
    {
        return $this->front_page;
    }

    public function setFrontPage(?string $front_page): self
    {
        $this->front_page = $front_page;

        return $this;
    }

    /**
     * @return Collection<int, BookEntry>
     */
    public function getBookEntries(): Collection
    {
        return $this->bookEntries;
    }

    public function addBookEntry(BookEntry $bookEntry): self
    {
        if (!$this->bookEntries->contains($bookEntry)) {
            $this->bookEntries[] = $bookEntry;
            $bookEntry->setBook($this);
        }

        return $this;
    }

    public function removeBookEntry(BookEntry $bookEntry): self
    {
        if ($this->bookEntries->removeElement($bookEntry)) {
            // set the owning side to null (unless already changed)
            if ($bookEntry->getBook() === $this) {
                $bookEntry->setBook(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }
}
