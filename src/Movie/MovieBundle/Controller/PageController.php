<?php

namespace Movie\MovieBundle\Controller;

use Movie\MovieBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Movie\MovieBundle\Entity\Movie;
use Symfony\Component\Validator\Constraints\File;

class PageController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $movies = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->findAll();
        return $this->render('@MovieMovie/Page/index.html.twig', array(
            'movies' => $movies
        ));
    }
    /*
     * End of indexAction
     */

    /**
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function newAction(Request $request)
    {
        $movie = new Movie();

        $form = $this->createFormBuilder($movie)
            ->setMethod('POST')
            ->add('title', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('director', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('summary', TextType::class, array(
                'attr' => array('class' => 'form-contrseol')))
            ->add('actors', TextType::class, array(
                'attr' => array('class' => 'form-control')))
            ->add('running_time', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('image_file', FileType::class, array(
                'mapped' => false,
                'label' => 'Upload image file for movie',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PNG or Jpeg file',
                    ])
                ],
                'attr' =>
                    array('class' => 'form-control'
                    )
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-2')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            echo '<pre>';
//            var_dump($request);
//            $file = $request->request->get('form');
//            var_dump($form); die;

            $title = $form['title']->getData();
            $director = $form['director']->getData();
            $summary = $form['summary']->getData();
            $actors = $form['actors']->getData();
            $running_time = $form['running_time']->getData();
            $image_file = $form->get('image_file')->getData();

            if ($image_file) {
                echo "image got";
                $originalFilename = pathinfo($image_file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image_file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image_file->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    // updates the 'image_filename' property to store the file name
                    // instead of its contents
                    $movie->setImageFile($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    echo "Exception: " . $e;
                }


            }

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
    /*
    * End of newAction
    */


//    public function searchAction()
//    {
//        $form = $this->createFormBuilder(null)
//            ->add('search', TextType::class)
//            ->getForm();
//
//        return $this->render('@MovieMovie/Page/search.html.twig', [
//            'form' => $form->createView()
//        ]);
//    }
    /*
     * End of searchAction
     */

    /**
     * @param Request $request
     * @param $id
     * @return Response|null
     */
    public function reviewAction(Request $request, $id)
    {
        $movie = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->find($id);

        $userId = $this->getUser()->getId();
        $reviewer = $this->getDoctrine()->getRepository('MovieMovieBundle:User')->find($userId);

        $newreview = new Review();

        $form = $this->createFormBuilder($newreview)
            ->setMethod('POST')
            ->add('review', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('rating', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Save Review',
                'attr' => array('class' => 'btn btn-primary mt-2')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review = $form['review']->getData();
            $rating = $form['rating']->getData();


            $newreview->setReview($review);
            $newreview->setRating($rating);
            $newreview->setMovie($movie);
            $newreview->setReviewer($reviewer);
            // Should I add movie to review or add review to movie?


            $em = $this->getDoctrine()->getManager();
            $em->persist($newreview);
            $em->flush();

            return $this->redirectToRoute('movie_show', array(
                'id' => $movie->getId()
            ));
        }

        return $this->render('@MovieMovie/Page/review.html.twig', array(
            'form' => $form->createView(),
            'movie' => $movie
        ));

    }
    /*
    * End of reviewAction
    */


    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response|null
     */
    public function editMovieAction(Request $request, $id)
    {
        $movie = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->find($id);

        $form = $this->createFormBuilder($movie)
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
            ->add('image_file', FileType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary mt-2')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form['title']->getData();
            $director = $form['director']->getData();
            $summary = $form['summary']->getData();
            $actors = $form['actors']->getData();
            $running_time = $form['running_time']->getData();
            $image_file = $form['image_file']->getData();

            $movie->setTitle($title);
            $movie->setDirector($director);
            $movie->setSummary($summary);
            $movie->setActors($actors);
            $movie->setRunningTime($running_time);
            $movie->setImageFile($image_file);

            $em = $this->getDoctrine()->getManager();
            $em->persist($movie);
            $em->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('@MovieMovie/Page/editMovie.html.twig', [
            'form' => $form->createView(),
            'movie' => $movie
        ]);

    }
    /*
    * End of editMovieAction
    */

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response|null
     */
    public function editReviewAction(Request $request, $id)
    {
//        $movie = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->find($id);

        $reviewToEdit = $this->getDoctrine()->getRepository('MovieMovieBundle:Review')->find($id);

        $form = $this->createFormBuilder($reviewToEdit)
//            Below give "Could not determine access type for property "id" in class "Movie\MovieBundle\Entity\Review"."
//            ->add('id', TextType::class, array('attr' =>
//                array('class' => 'form-control', 'disabled' => 'true')))
            ->add('review', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('rating', TextType::class, array('attr' =>
                array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary mt-2')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review = $form['review']->getData();
            $rating = $form['rating']->getData();

            $reviewToEdit->setReview($review);
            $reviewToEdit->setRating($rating);


            $em = $this->getDoctrine()->getManager();
            $em->persist($reviewToEdit);
            $em->flush();

            return $this->redirectToRoute('movie_show', array(
                'id' => $reviewToEdit->getId()
            ));
        }

        return $this->render('@MovieMovie/Page/editReview.html.twig', [
            'form' => $form->createView(),
            'review' => $reviewToEdit
        ]);

    }
    /*
    * End of editReviewAction
    */

    /**
     * @param $id
     * @return Response|null
     */
    public function showAction($id)
    {
        $repository = $this->getDoctrine()
            ->getRepository('MovieMovieBundle:Review');
        $movie = $this->getDoctrine()->getRepository('MovieMovieBundle:Movie')->find($id);
        $movieId = $movie->getId();

//         createQueryBuilder() automatically selects FROM AppBundle:Movie
//         and aliases it to "r"
        $query = $repository->createQueryBuilder('r')
            ->setParameters(array(
                'movie' => $movieId))
            ->where('r.movie = :movie')
            ->orderBy('r.id', 'ASC')
            ->getQuery();

        $reviews = $query->getResult();
// to get just one result:
// $movie = $query->setMaxResults(1)->getOneOrNullResult();

        return $this->render('@MovieMovie/Page/show.html.twig', array('movie' => $movie, 'reviews' => $reviews));

    }
    /*
    * End of showAction
    */




    // This function is not currently working properly - not required in the assignment spec
    /**
     * @param $id
     * @return Response|null
     */
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
    /*
    * End of deleteAction
    */

    /**
     * @param $id
     * @return Response|null
     */
    public function userPageAction($id)
    {
        $user = $this->getDoctrine()->getRepository('MovieMovieBundle:User')->find($id);
        $reviews = $this->getDoctrine()->getRepository('MovieMovieBundle:Review')->find($id);

        return $this->render('@MovieMovie/Page/userpage.html.twig', array('user' => $user, 'reviews' => $reviews));
    }
    /*
    * End of userPageAction
    */

    /**
     * @return Response|null
     */
    public function aboutAction()
    {
        return $this->render('@MovieMovie/Page/about.html.twig');
    }
    /*
    * End of aboutAction
    */

    /**
     * @return Response|null
     */
    public function loginAction()
    {
        return $this->render('@MovieMovie/Page/login_content.html.twig');
    }
    /*
    * End of loginAction
    */

    /**
     * @return Response|null
     */
    public function registerAction()
    {
        return $this->render('@MovieMovie/Page/register.html.twig');
    }
    /*
    * End of registerActionAction
    */


}
