<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/book", name="book_index")
     */
    public function index(): Response
    {
        $books = $this->em->getRepository(Book::class)->findAll();
        $entries = $this->em->getRepository(BookEntry::class)->findAll();
        return $this->render('book/index.html.twig', [
            'books' => $books,
            'entries' => $entries
        ]);
    }

    /**
     * @Route("/book/entry/{id}", name="book_entry")
     */
    public function book_entry(BookEntry $bookEntry) {
        return $this->render('book/book-entry.html.twig', ['entry' => $bookEntry]);
    }

}
