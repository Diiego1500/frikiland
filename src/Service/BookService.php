<?php

namespace App\Service;
use App\Entity\BookEntry;
use Doctrine\ORM\EntityManagerInterface;

class BookService {
    private $entityManager;

    /**
     * @param $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function entry_exist($entry_id){
        $entry = $this->entityManager->getRepository(BookEntry::class)->find($entry_id);
        return $entry != null ? $entry : false;
    }
}