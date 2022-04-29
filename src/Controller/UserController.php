<?php

namespace App\Controller;

use App\Entity\Interaction;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }


    /**
     * @Route("/user/{id}", name="app_user")
     */
    public function index(User $user): Response
    {
        $user_posts = $this->em->getRepository(Post::class)->findBy(['user' => $user]);
        $saved_posts = $this->em->getRepository(Interaction::class)->findBy(['user' => $user, 'user_favorite' => true]);
        return $this->render('user/index.html.twig', [
            'user_posts' => $user_posts,
            'saved_posts' => $saved_posts
        ]);
    }
}
