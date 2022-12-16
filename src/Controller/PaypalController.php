<?php

namespace App\Controller;


use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaypalController extends AbstractController
{
    private $em;

    /**
     * @param $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @Route("/success", name="paypal_success")
     */
    public function paypal_success(Request $request): Response {
        $details = $request->request->get('details');
        $course_name = $request->request->get('course-name');
        $course_id = $request->request->get('course-id');
        $user = $this->getUser();
        $transaction = new Transaction($course_name, $user, $details);
        $this->add_course_to_user($user, $course_id);
        $this->em->persist($transaction);
        $this->em->flush();
        return $this->redirectToRoute('app_courses');
    }


    public function add_course_to_user($user, $course_id) {
        $user_courses = json_decode($user->getCourseAccess());
        $user_courses[] = $course_id;
        $user->setCourseAccess(json_encode($user_courses));
    }
}

