<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\PodcastComment;
use App\Form\PodcastCommentType;
use App\Form\PodcastType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PodcastController extends AbstractController
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
     * @Route("/admin/podcast", name="AdminPodcast")
     */
    public function AdminPodcast(Request $request, SluggerInterface $slugger){
        $podcast = new Podcast();
        $podcasts = $this->em->getRepository(Podcast::class)->findAll();
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $source = $form->get('src')->getData();
            if ($source) {
                $originalFilename = pathinfo($source->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$source->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $source->move(
                        $this->getParameter('podcast_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('there is a mistake with your Podcast File');
                }
                $podcast->setSrc($newFilename);
            }
           $this->em->persist($podcast);
           $this->em->flush();
           return $this->redirectToRoute('AdminPodcast');
        }
        return $this->render('podcast/admin_podcast.html.twig', ['form' => $form->createView(), 'podcasts' => $podcasts]);
    }

    /**
     * @Route("/admin/podcast/delete/{id}", name="DeletePodcast")
     */
    public function DeletePodcast(Podcast $podcast) {
        $this->em->remove($podcast);
        $this->em->flush();
        return $this->redirectToRoute('AdminPodcast');
    }

    /**
     * @Route("/podcast", name="app_podcast")
     */
    public function index(Request $request): Response
    {
        $comment = new PodcastComment();
        $podcasts = $this->em->getRepository(Podcast::class)->findAll();
        $podcast_comments = $this->em->getRepository(PodcastComment::class)->findAll();
        $form = $this->createForm(PodcastCommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser())->setCreationDate(new \DateTime());
            $this->em->persist($comment);
            $this->em->flush();
            $this->addFlash('success', '¡Comentario Creado! ¡El conocimiento es poder!');
            return  $this->redirectToRoute('app_podcast');
        }
        return $this->render('podcast/index.html.twig', [
            'podcasts' => $podcasts,
            'podcast_comments'=> $podcast_comments,
            'form' => $form->createView()
        ]);
    }
}
