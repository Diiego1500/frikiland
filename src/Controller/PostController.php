<?php

namespace App\Controller;

use App\Entity\Interaction;
use App\Entity\Post;
use App\Entity\User;
use App\Form\InteractionType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function index(Request $request, SluggerInterface $slugger): Response {
        $post = new Post();
        $posts = $this->em->getRepository(Post::class)->findAll();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = str_replace(" ", "-", $form->get('title')->getData());
            $user = $this->getUser();
            $date = new \DateTime();
            $file = $form->get('file')->getData();
            if($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('there is a mistake with this file');
                }
                $post->setFile($newFilename);
            }
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
     * @Route ("/post/details/{id}/{url}", name="postDetails")
     */
    public function postDetails(Post $post, Request $request) {
        $user = $this->getUser();
        $interaction = $this->em->getRepository(Interaction::class)->findOneBy(['post' => $post, 'user'=>$user]);
        if ($interaction == null) {
            $interaction = new Interaction();
        }
        $interactions = $this->em->getRepository(Interaction::class)->findBy(['post' => $post->getId()]);
        $interaction_form = $this->createForm(InteractionType::class, $interaction);
        $interaction_form->handleRequest($request);
        if ($interaction_form->isSubmitted() && $interaction_form->isValid()) {
            $interaction->setUser($user);
            $post->addInteraction($interaction);
            $this->em->persist($interaction);
            $this->em->flush();
            return  $this->redirectToRoute('postDetails', ['id' => $post->getId(), 'url' => $post->getUrl()]);
        }
        return $this->render('post/post-details.html.twig', [
            'interaction_form' => $interaction_form->createView(),
            'post' => $post,
            'interactions' => $interactions
        ]);
    }

    /**
     * @Route ("/post/edit/{id}", name="postEdit")
     */
    public function postEdit(Post $post, Request $request) {
        $user = $this->getUser();
        if ($post->getUser() == $user) {
            $form = $this->createForm(PostType::class, $post);
            $form->remove('file');
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->flush();
                return $this->redirectToRoute('postDetails', ['id' => $post->getId(), 'url' => $post->getUrl()]);
            }
            return $this->render('post/post-edit.html.twig', ['form' =>$form->createView()]);
        }
        throw new \Exception('You are not allowed to this action');
    }

    /**
     * @Route ("/post/delete/{id}", name="postDelete")
     */
    public function postDelete(Post $post) {
        $user = $this->getUser();
        if ($post->getUser() == $user){
           $this->em->remove($post);
           $this->em->flush();
           return $this->redirectToRoute('index');
        }
        throw new \Exception('You are not allowed to this action');
    }
}
