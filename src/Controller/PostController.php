<?php

namespace App\Controller;

use App\Entity\Interaction;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(Request $request): Response {
        $post = new Post();
        $posts = $this->em->getRepository(Post::class)->findAll();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = str_replace(" ", "-", $form->get('title')->getData());
            $user = $this->em->getRepository(User::class)->find(1);
            $date = new \DateTime();
            $post->setUser($user)->setCreationDate($date)->setUrl($url);
            $this->em->persist($post);
            $this->em->flush();
            return $this->redirectToRoute('index');
        }
        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts
        ]);
    }

    /**
     * @Route ("/post/{id}/{url}", name="postDetails")
     */
    public function postDetails($id, $url) {
        $post = $this->em->getRepository(Post::class)->find($id);
        $interactions = $this->em->getRepository(Interaction::class)->findBy(['post' => $post->getId()]);
        return $this->render('post/post-details.html.twig', ['post' => $post, 'interactions' => $interactions]);
    }
}
