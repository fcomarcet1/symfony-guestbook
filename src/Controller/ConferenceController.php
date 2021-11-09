<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\HttpClient\SpamChecker;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class ConferenceController extends AbstractController
{
    private Environment $twig;
    private EntityManagerInterface $entityManager;
    private SpamChecker $spamChecker;

    public function __construct(
        Environment $twig,
        EntityManagerInterface $entityManager,
        SpamChecker $spamChecker)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->spamChecker = $spamChecker;
    }

    /**
     * @Route("/", name="homepage")
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAllOrderByYear(),
        ]));
    }

    /**
     * @Route("/conference/{slug}", name="conference")
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    public function show(
        Request           $request,
        Conference        $conference,
        CommentRepository $commentRepository,
        string $photoDir
    ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            // upload photo
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);

            // Check if the comment is a spam
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];
           if ($this->spamChecker->getSpamScore($comment, $context) === 2){
               throw new \RuntimeException('Blatant spam, go away!');
           }


            $this->entityManager->flush();

            return $this->redirectToRoute('conference', [
                'slug' => $conference->getSlug()
            ]);
        }


        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return new Response($this->twig->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView(),
        ]));
    }
}
