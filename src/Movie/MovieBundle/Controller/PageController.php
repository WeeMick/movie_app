<?php

namespace Movie\MovieBundle\Controller;

use Movie\MovieBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Movie\MovieBundle\Entity\Movie;

class PageController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
//        $movies = array(
//            array(
//                'name' => 'Jaws',
//                'review' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consectetur dolores iure labore obcaecati repudiandae. Tempora?',
//                'rating' => 3
//            ),

        $movies = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->findAll();
        return $this->render('@MovieMovie/Page/index.html.twig', array(
            'movies' => $movies
        ));


    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function newAction(Request $request)
    {
        // creates a movie record and adds it to the database
//        $em = $this->getDoctrine()->getManager();
          $movie = new Movie();
//        $movie->setTitle('Look, a new Movie');
//        $movie->setSummary('Summary of a new Movie');
//        $movie->setDetailedDescription('Blah blah blah');
//        $movie->setDateAdded(new \DateTime('now'));
//        $movie->setRating(2.1);
//
//        $em->persist($movie);
//        $em->flush();
//
//        return $this->redirectToRoute('movie_index');

        $form = $this->createFormBuilder($movie)
            ->setMethod('POST')
            ->add('title', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('director', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('summary', TextType::class, array(
                'attr' => array('class' => 'form-control')))
            ->add('actors', TextType::class, array(
                'attr' => array('class' => 'form-control')))
            ->add('running_time', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-2')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $title = $form['title']->getData();
            $director = $form['director']->getData();
            $summary = $form['summary']->getData();
            $actors = $form['actors']->getData();
            $running_time = $form['running_time']->getData();

            $movie->setTitle($title);
            $movie->setDirector($director);
            $movie->setSummary($summary);
            $movie->setActors($actors);
            $movie->setRunningTime($running_time);

            $em = $this->getDoctrine()->getManager();
            $em->persist($movie);
            $em->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('@MovieMovie/Page/new.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/movie/new")
     */
    public function newReviewAction()
    {
        $review = new Review();

        $form = $this->createFormBuilder($review)
            ->add('movie', TextType::class, array('attr' =>
                array('class' => 'form-control')))
//            ->add('director', TextType::class, array('attr' =>
//                array('class' => 'form-control')))
//            ->add('summary', TextType::class, array(
//                'required' => false,
//                'attr' => array('class' => 'form-control')))
            ->add('review', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('rating', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Review',
                'attr' => array('class' => 'btn btn-primary mt-2')))
            ->getForm();

        return $this->render('@MovieMovie/Page/newreview.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    public function showAction($id)
    {

        $repository = $this->getDoctrine()
            ->getRepository('MovieMovieBundle:Review');

        // createQueryBuilder() automatically selects FROM AppBundle:Movie
        // and aliases it to "m"
//        $query = $repository->createQueryBuilder('m')
//            ->where('m.movie_id = movie')
//            ->orderBy('m.rating', 'ASC')
//            ->getQuery();

//        $products = $query->getResult();
// to get just one result:
// $product = $query->setMaxResults(1)->getOneOrNullResult();

        $movie = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->find($id);

//need to get movie id of movie passed in and then search Review db for records with movie_id
        $review = $this->getDoctrine()->getRepository('MovieMovieBundle:Review')->find($movie);
        return $this->render('@MovieMovie/Page/show.html.twig', array('movie' => $movie, 'review' => $review));

    }

    public function deleteAction($id)
    {
        $movie = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($movie);
        $em->
        $em->flush();

        // Deleting movie will need to delete all reviews for that movie too
        return $this->render('@MovieMovie/Page/show.html.twig', array('movie' => $movie));

    }

    public function userPageAction($id)
    {
        $user = $this->getDoctrine()->getRepository('MovieMovieBundle:User')->find($id);
        $reviews = $this->getDoctrine()->getRepository('MovieMovieBundle:Review')->find($id);

        return $this->render('@MovieMovie/Page/userpage.html.twig', array('user' => $user,'reviews' => $reviews));
    }

    public function aboutAction()
    {
        return $this->render('@MovieMovie/Page/about.html.twig');
    }

    public function loginAction()
    {
        return $this->render('@MovieMovie/Page/login_content.html.twig');
    }

    public function registerAction()
    {
        return $this->render('@MovieMovie/Page/register.html.twig');
    }

    /**
     * @Route("/edit/{id}")
     */
    public function editAction()
    {
        return $this->render('@MovieMovie/Page/edit.html.twig');
    }


}
