<?php

namespace App\Controller;

use App\Entity\Interaction;
use App\Entity\Post;
use App\Entity\User;
use App\Form\ChangePhotoType;
use App\Form\DescriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function index(User $user, SluggerInterface $slugger, Request $request): Response
    {
        $user_posts = $this->em->getRepository(Post::class)->findBy(['user' => $user]);
        $saved_posts = $this->em->getRepository(Interaction::class)->findBy(['user' => $user, 'user_favorite' => true]);
        $form_description = $this->createForm(DescriptionType::class);
        $form_photo = $this->createForm(ChangePhotoType::class);

        $form_description->handleRequest($request);
        $form_photo->handleRequest($request);

        if ($form_description->isSubmitted() && $form_description->isValid()) {
            $new_description = $form_description->get('description')->getData();
            $user->setDescription($new_description);
            $this->em->flush();
        }

        if($form_photo->isSubmitted() && $form_photo->isValid()){
            $photo = $form_photo->get('photo')->getData();
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('There is an error.');
                }
                $user->setPhoto($newFilename);
                $this->em->flush();
            }
        }
        return $this->render('user/index.html.twig', [
            'form_photo' => $form_photo->createView(),
            'form_description' => $form_description->createView(),
            'user_posts' => $user_posts,
            'saved_posts' => $saved_posts,
            'user' => $user
        ]);
    }
}
