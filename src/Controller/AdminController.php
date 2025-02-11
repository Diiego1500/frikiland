<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookEntry;
use App\Entity\Course;
use App\Entity\CourseClass;
use App\Entity\Post;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\BookEntryType;
use App\Form\BookType;
use App\Form\CourseClassType;
use App\Form\CourseType;
use App\Form\NotificationType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
     * @Route("/admin/news/notifications", name="news_notification")
     */
    public function news_notification(Request $request, MailerInterface $mailer): Response {
        $form = $this->createForm(NotificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $title = $form->get('title')->getData();
            $body = $form->get('body')->getData();
            $users_emails = $this->em->getRepository(User::class)->findAllEmails();
            $email = (new Email())
                ->from('notificaciones@frikyland.com')
                ->to(...$users_emails)
                ->subject($title)
                ->text('do not answer to this email.')
                ->html("<p> $body </p>");
            $mailer->send($email);
            $this->addFlash('success', 'Tienes super poderes! Has enviado un correo MASIVAMENTE');
            return  $this->redirectToRoute('news_notification');
        }
        return $this->render('admin/news-notifications.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/admin/delete/user/{id}", name="delete_user")
     */
    public function delete_user(User $user) {
        $user_posts = $user->getPosts();
        $user_comments = $user->getInteractions();
        $user_podcast_comment = $user->getPodcastComments();
        foreach ($user_posts as $post) {
            $this->em->remove($post);
        }

        foreach ($user_comments as $comment) {
            $this->em->remove($comment);
        }

        foreach ($user_podcast_comment as $podcast_comment) {
            $this->em->remove($podcast_comment);
        }

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

    /**
     * @Route("/admin/books", name="admin_books")
     */
    public function admin_books(Request $request, SluggerInterface $slugger){
        $book = new Book();
        $book_entry = new BookEntry();
        $created_books = $this->em->getRepository(Book::class)->findAll();
        $created_books_entries = $this->em->getRepository(BookEntry::class)->findAll();
        $form_books = $this->createForm(BookType::class, $book);
        $form_entry = $this->createForm(BookEntryType::class, $book_entry);
        $form_books->handleRequest($request);
        $form_entry->handleRequest($request);
        if ($form_books->isSubmitted() && $form_books->isValid()) {
            $this->em->persist($book);
            $front_page = $form_books->get('front_page')->getData();
            if ($front_page) {
                $originalFilename = pathinfo($front_page->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$front_page->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $front_page->move(
                        $this->getParameter('books_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('There is an error.');
                }
                $book->setFrontPage($newFilename);
                $this->em->flush();
            }
            $this->em->flush();
            $this->addFlash('success', 'Libro Creado');
            return  $this->redirectToRoute('admin_books');
        }
        if ($form_entry->isSubmitted() && $form_entry->isValid()) {
            $this->em->persist($book_entry);

            $this->addFlash('success', 'Entrada Creada');
            $this->em->flush();
            return $this->redirectToRoute('admin_books');

        }
        return $this->render('admin/books.html.twig', [
            'form_books' => $form_books->createView(),
            'form_entry' => $form_entry->createView(),
            'created_books' => $created_books,
            'created_books_entries' => $created_books_entries
        ]);
    }

    /**
     * @Route("/admin/edit/entry/{id}", name="edit_entry")
     */
    public function edit_books(BookEntry $bookEntry, Request $request) {
        $form = $this->createForm(BookEntryType::class, $bookEntry);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Entrada Editada');
            return $this->redirectToRoute('admin_books');
        }

        return $this->render('admin/edit-entry.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/admin/posts", name="admin_posts")
     */
    public function admin_posts(PaginatorInterface $paginator, Request $request) {
        $query = $this->em->getRepository(Post::class)->findAllPost();
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('admin/post-list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route ("/admin/fix/post/{id}", name="admin_fix_post")
     */
    public function admin_fix_post(Post $post){
        $title = $post->getTitle();
        $post->setFixedPost(!$post->getFixedPost());
        $this->em->flush();
        $this->addFlash('success', "Post $title FIJADO con exito");
        return $this->redirectToRoute('admin_posts');
    }

    /**
     * @Route ("/admin/post/edit/{id}", name="admin_post_edit")
     */
    public function admin_post_edit(Post $post, Request $request){
        $form = $this->createForm(PostType::class, $post);
        $form->remove('file');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $title = $post->getTitle();
            $this->addFlash('success', "Post $title EDITADO con exito");
            return $this->redirectToRoute('admin_posts');
        }
        return $this->render('post/post-edit.html.twig', ['form' => $form->createView(), 'post' => $post]);
    }

    /**
     * @Route("/admin/delete/post/{id}", name="delete_post")
     */
    public function delete_post(Post $post){
        $this->em->remove($post);
        $this->em->flush();
        $this->addFlash('success', 'Post Eliminado con exito');
        return $this->redirectToRoute('admin_posts');
    }

    /**
     * @Route("/admin/delete/entry/{id}", name="delete_entry")
     */
    public function delete_entry(BookEntry $bookEntry) {
        $this->em->remove($bookEntry);
        $this->em->flush();
        return $this->redirectToRoute('admin_books');
    }

    /**
     * @Route("/admin/courses", name="admin_courses")
     */
    public function admin_courses(Request $request, SluggerInterface $slugger) {
        $course = new Course();
        $course_class = new CourseClass();

        $courses = $this->em->getRepository(Course::class)->findAll();

        $form = $this->createForm(CourseType::class, $course);
        $form_class = $this->createForm(CourseClassType::class, $course_class);

        $form->handleRequest($request);
        $form_class->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $image = $form->get('image')->getData();
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                try {
                    $image->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('a toruble with your file');
                }
                $course->setImage($newFilename);
            }
            $this->em->persist($course);
            $this->em->flush();
            return $this->redirectToRoute('admin_courses');
        }
        if($form_class->isSubmitted() && $form_class->isValid()) {
            $video = $form_class->get('video')->getData();
            if ($video) {
                $originalFilename = pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newVideoname = $safeFilename.'-'.uniqid().'.'.$video->guessExtension();
                try {
                    $video->move(
                        $this->getParameter('courses_directory') . '/' . str_replace(' ', '_', $course->getTitle()  ),
                        $newVideoname
                    );
                } catch (FileException $e) {
                    throw new \Exception('a toruble with your file');
                }
                $course_class->setVideo($newVideoname);
            }
            $this->em->persist($course_class);
            $this->em->flush();
            return $this->redirectToRoute('admin_courses');
        }

        return $this->render('admin/add-course.html.twig', [
            'form' => $form->createView(),
            'form_class' => $form_class->createView(),
            'courses' => $courses
        ]);
    }

    /**
     * @Route("/admin/delete/course/{id}", name="delete_course")
     */
    public function delete_course(Course $course) {
        $course_classes = $course->getCourseClasses();
        foreach ($course_classes as $course_class) {
            $this->em->remove($course_class);
        }
        $this->em->remove($course);
        $this->em->flush();
        return $this->redirectToRoute('admin_courses');
    }

    /**
     * @Route("/admin/edit/course/{id}", name="edit_course")
     */
    public function edit_course(Course $course, Request $request){
        $form = $this->createForm(CourseType::class, $course);
        $form->remove('image');
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($course);
            $this->em->flush();
            return $this->redirectToRoute('edit_course', ['id' => $course->getId()]);
        }
        return $this->render('admin/edit-course.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/admin/delete/course/class/{id}", name="delete_course_class")
     */
    public function delete_course_class(CourseClass $courseClass){
        $this->em->remove($courseClass);
        $this->em->flush();
        return $this->redirectToRoute('admin_courses');
    }

    /**
     * @Route("/admin/edit/course/class/{id}", name="edit_course_class")
     */
    public function edit_course_class(CourseClass $courseClass, Request $request){
        $form_class = $this->createForm(CourseClassType::class, $courseClass);
        $form_class->remove('video');
        $form_class->handleRequest($request);
        if($form_class->isSubmitted() && $form_class->isValid()) {
            $this->em->persist($courseClass);
            $this->em->flush();
            return $this->redirectToRoute('admin_courses');
        }
        return $this->render('admin/edit-course-class.html.twig', ['form' => $form_class->createView()]);
    }

    /**
     * @Route("/admin/add/course/{user_id}/{course_id}", name="add_course")
     */
    public function add_course_to_user($user_id, $course_id){
        $user = $this->em->getRepository(User::class)->find($user_id);
        $user_courses = json_decode($user->getCourseAccess());
        $user_courses[] = $course_id;
        $user->setCourseAccess(json_encode($user_courses));
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse(['Curso Agregado exitosamente']);
    }


    /**
     * @Route("/admin/watch/transactions", name="watch_transactions")
     */
    public function watch_transactions(){
        $transactions = $this->em->getRepository(Transaction::class)->findAll();
        return $this->render('admin/watch-transactions.html.twig', ['transactions' => $transactions]);
    }



}
