<?php

namespace App\Controller;

use App\Entity\Interaction;
use App\Entity\Post;
use App\Entity\User;
use App\Form\FilterType;
use App\Form\InteractionType;
use App\Form\PostType;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
     * @Route("/{filter}", name="index")
     */
    public function index(Request $request, SluggerInterface $slugger, PaginatorInterface $paginator, $filter='none'): Response {
        $post = new Post();
        $query = $this->em->getRepository(Post::class)->findAllPost();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->shared_render($post, $request, $slugger, $pagination);
    }

    /**
     * @Route("/filter/{filter}", options={"expose"=true}, name="filter_posts")
     */
    public function filter_posts(Request $request, SluggerInterface $slugger, PaginatorInterface $paginator, $filter='none') {
        $post = new Post();
        $query = $this->em->getRepository(Post::class)->findPostByType($filter);
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        $this->addFlash('success', "Ahora estás visualizando los posts de por el item seleccionado");
        return $this->shared_render($post, $request, $slugger, $pagination);
    }

    public function shared_render($post, $request, $slugger, $pagination){
        $filter_form = $this->createForm(FilterType::class);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $filter_form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = str_replace(" ", "-", $form->get('title')->getData());
            $user = $this->getUser();
            $date = new \DateTime();
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

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
        if ($filter_form->isSubmitted() && $filter_form->isValid()) {
            $seleccion = $filter_form->get('filter_type')->getData();
            if ($seleccion == 'none') {
                return $this->redirectToRoute('index', ['filter' => $seleccion]);
            }
            return $this->redirectToRoute('filter_posts', ['filter' => $seleccion]);
        }
        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'filter_form' => $filter_form->createView(),
            'posts' => $pagination
        ]);
    }

    /**
     * @Route ("/post/details/{id}/{url}", name="postDetails")
     */
    public function postDetails(Post $post, Request $request, MailerInterface $mailer) {
        $user = $this->getUser();
        $interaction = $this->em->getRepository(Interaction::class)->findOneBy(['post' => $post, 'user' => $user]);

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
            $email = (new Email())
                ->from('notificaciones@frikyland.com')
                ->to($post->getUser()->getEmail())
                ->subject('Han comentado tu Post en Frikyland!')
                ->text('do not answer to this email.')
                ->html("<p> <strong>Alguien ha comentado tu publicación en <a href='https://frikyland.com/'>Frikyland!</a> Entra a nuestro  <a href='https://frikyland.com/'>sitio web</a> y echale un vistaso a tus publicaciones :D</strong></p>");
            $mailer->send($email);
            $this->em->flush();
            return $this->redirectToRoute('postDetails', ['id' => $post->getId(), 'url' => $post->getUrl()]);
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
            return $this->render('post/post-edit.html.twig', ['form' => $form->createView(), 'post' => $post]);
        }
        throw new \Exception('You are not allowed to this action');
    }

    /**
     * @Route ("/post/delete/{id}", options={"expose"=true}, name="postDelete")
     */
    public function postDelete(Post $post) {
        $user = $this->getUser();
        if ($post->getUser() == $user) {
            $this->em->remove($post);
            $this->em->flush();
            return $this->redirectToRoute('index');
        }
        throw new \Exception('You are not allowed to this action');
    }

    /**
     * @Route ("/post/save/{id}", options={"expose"=true}, name="postSave")
     */
    public function postSave(Post $post, Request $request) {
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            $interaction = $this->em->getRepository(Interaction::class)->findOneBy(['post' => $post, 'user' => $user]);
            if ($interaction == null) {
                $interaction = new Interaction();
                $interaction->setUser($user)->setPost($post)->setUserFavorite(true)->setComment(null);
                $this->em->persist($interaction);
            }
            $interaction->setUserFavorite(true);
            $this->em->flush();
            return new JsonResponse(['success' => true]);
        }
        throw new Exception('this is not an ajax call');
    }
}
