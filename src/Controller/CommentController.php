<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\BlogRepository;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CommentRepository $commentRepository, BlogRepository $blogRepository): JsonResponse
    {
        if ( $request->isMethod('POST')){ // On vérifie que l'ajax est en mode post


            $form = $request->getContent(); // On récupère les données de l'ajax.
            $data = []; // On precise que le conteneur des données est un tableau.
            $data = json_decode($form, true); // On décode les données de l'ajax que l'on met dans le tableau
            $user = $this->getUser(); // On recupère lors de la création du commentaire l'utilisateur connecté
            $blogId = $data['id']; // On recupère l'id du blog afin de l'injecter dans l'objet Comment.
            $content = $data['content']; // On récupère le contenu HTML du WYSIWYG (What you see is what you get) de TinyMCE

            $blog = $blogRepository->findOneById($blogId); // On recupère le blog à partir de l'id sur le front

            $comment = new Comment();
            $comment->setUser($user); // On injecte dans le commentaire l'utilisateur connecté
            $comment->setContent($content); // On injecte le contenu du commentaire.
            $comment->setBlog($blog); // On relie le commentaire au blog ou il a été créé
            $commentRepository->save($comment, true); // On fait persister les données dans la BDD

            return new JsonResponse([
                'status' => "ok", // On retourne un message de reussite
                'content' => $content,
                'user' => $user->getUserIdentifier(),
                'date' => $comment->getCreateAt(),

            ]);
        }
        else
        {
            return new JsonResponse([
                'status' => "error", // On retourn un message d'erreur.
            ]);
        }

//        $comment = new Comment();
//        $form = $this->createForm(CommentType::class, $comment);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $commentRepository->save($comment, true);
//
//            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->renderForm('comment/new.html.twig', [
//            'comment' => $comment,
//            'form' => $form,
//        ]);
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment, true);
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
