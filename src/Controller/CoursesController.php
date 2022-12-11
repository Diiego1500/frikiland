<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\CourseClass;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoursesController extends AbstractController
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
     * @Route("/courses", name="app_courses")
     */
    public function index(): Response {
        $courses = $this->em->getRepository(Course::class)->findAll();
        return $this->render('courses/index.html.twig', [
            'courses' => $courses
        ]);
    }

    /**
     * @Route("/courses/{id}", name="buy_course")
     */
    public function buy_course(Course $course): Response {
        return $this->render('courses/course-buy.html.twig', [
            'course' => $course
        ]);
    }


    /**
     * @Route("/courses/class/{id}", name="class_visualization")
     */
    public function class_visualization(CourseClass $courseClass) {
        $user = $this->getUser();
        if (
            $this->isGranted('ROLE_ADMIN') // si es admin
            or ($courseClass->getCourse()->getIsFree()  and $this->isGranted('IS_AUTHENTICATED_FULLY') )// si es gratis y está autenticado
            or ($this->isGranted('IS_AUTHENTICATED_FULLY') and in_array($courseClass->getCourse()->getId(), json_decode($user->getCourseAccess()))) // si lo compró y está autenticado
            ) {
            return $this->render('courses/view-class.html.twig', [
                'courseClass' => $courseClass
            ]);
        } else {
            return $this->render('courses/please-buy-this-course.html.twig',
            ['course_id' => $courseClass->getCourse()->getId()]
            );
        }
    }



}
