<?php

namespace App\Controller;

use App\Database\Biblioteka;
use App\Form\AddAuthorType;

use App\Form\PozycjaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/autor")
 */
class AutorController extends AbstractController
{
    /**
     * @Route("", name="app_autor")
     */
    public function index(): Response
    {
        $biblioteka = new Biblioteka();
        $authorsArray = $biblioteka->getAuthors();
        $popularniAutorzy = $biblioteka->popularniAutorzy();
        return $this->render('autor/index.html.twig', [
            'authors_array' => $authorsArray,
            'popularni_autorzy' => $popularniAutorzy,
        ]);
    }

    /**
     * @Route("/dodaj", name="app_autor_add")
     */
    public function add(Request $request): Response
    {
        $addAuthorForm = $this->createForm(AddAuthorType::class);

        $addAuthorForm->handleRequest($request);
        if($addAuthorForm->isSubmitted() && $addAuthorForm->isValid()) {
            $formData = $addAuthorForm->getData();

            $bibliotekaDb = new Biblioteka();

            $addAuthorResult = $bibliotekaDb->addAuthor($formData['imie'], $formData['nazwisko']);
            $statusString = pg_result_status($addAuthorResult);
            $queryErrors = pg_result_error($addAuthorResult);

//            $result['raw'] = $addAuthorResult;
//            $result['status'] = $statusString;
//            $result['errors'] = $queryErrors;
//            $result['field_errors'] = $bibliotekaDb->getResultErrorDetails($addAuthorResult);
            if($statusString == 1) {
                return $this->redirectToRoute('app_autor');
            } else {
                $addAuthorForm->addError(
                    new FormError($queryErrors)
                );
            }
        } else {
            $formData = null;
            $result = null;
        }

        return $this->render('autor/add.html.twig', [
            'add_author_form' => $addAuthorForm->createView()
        ]);
    }

    /**
     * @Route("/{autorId}", name="author_details")
     */
    public function details($autorId, Request $request) {
        $biblioteka = new Biblioteka();
        $autorInfo = $biblioteka->getAuthorInfo($autorId);
        if($autorInfo == null) {
            throw new NotFoundHttpException('autor nie istnieje');
        }
        $books = $biblioteka->getAuthorBooks($autorId);
        return $this->render('autor/details.html.twig', [
            'autor_id'=> $autorId,
            'info' => $autorInfo,
            'pozycje' => $books
        ]);
    }

    /**
     * @Route("/{autorId}/dodaj-pozycje", name="app_author_add_book")
     */
    public function addBook($autorId, Request $request) {
        $biblioteka = new Biblioteka();
        $autorInfo = $biblioteka->getAuthorInfo($autorId);
        if($autorInfo == null) {
            throw new NotFoundHttpException('autor nie istnieje');
        }
        $addBookForm = $this->createForm(PozycjaType::class);
        $addBookForm->handleRequest($request);
        if($addBookForm->isSubmitted() && $addBookForm->isValid()) {
            $pozycjaData = $addBookForm->getData();
            $dataWydania = $pozycjaData['data_wydania']->format('Y-m-d');
            $addPozycjaResult = $biblioteka->addPozycja($autorId, $pozycjaData['ISBN'], $pozycjaData['tytul'], $dataWydania);
            $statusString = pg_result_status($addPozycjaResult);
            $queryErrors = pg_result_error($addPozycjaResult);
            if($statusString == 1) {
                return $this->redirectToRoute('author_details', [
                    'autorId'=>$autorId
                ]);
            } else {
                $addBookForm->addError(
                    new FormError($queryErrors)
                );
            }
        }
        return $this->render('autor/add_book.html.twig', [
            'autor_id'=> $autorId,
            'info' => $autorInfo,
            'add_book_form' => $addBookForm->createView()
        ]);
    }
}
