<?php
namespace App\Twig;

use App\Service\BookService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension {
    private $book_service;

    /**
     * @param $book_service
     */
    public function __construct(BookService $book_service) {
        $this->book_service = $book_service;
    }

    public function getFunctions() {
        return [
            new TwigFunction('entry_exist' , [ $this, 'entry_exist'])
        ];
    }

    public function entry_exist($entry_id) {
        return $this->book_service->entry_exist($entry_id);
    }
}