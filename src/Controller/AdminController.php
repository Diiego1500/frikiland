<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
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
     * @Route("/admin/users", name="admin_users")
     */
    public function admin_users(): Response {
        $users =  $this->em->getRepository(User::class)->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/delete/user/{id}", name="delete_user")
     */
    public function delete_user(User $user) {
        $this->em->remove($user);
        $this->em->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/admin/trusted/user/{id}", name="trusted_user")
     */
    public function is_trusted_user(User $user){
        $user->setIsTrusted(!$user->getIsTrusted());
        $this->em->flush();
        return $this->redirectToRoute('admin_users');
    }

}
