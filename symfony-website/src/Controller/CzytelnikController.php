<?php

namespace App\Controller;

use App\Database\Biblioteka;
use App\Form\CzytelnikType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CzytelnikController extends AbstractController
{
    /**
     * @Route("/czytelnik", name="app_czytelnik")
     */
    public function index(): Response
    {
        $biblioteka = new Biblioteka();
        $czytelnicyArray = $biblioteka->getCzytelnicy();
        return $this->render('czytelnik/index.html.twig', [
            'czytelnicy' => $czytelnicyArray,
        ]);
    }

    /**
     * @Route("/czytelnik/dodaj", name="czytelnik_dodaj")
     */
    public function dodajCzytelnika(Request $request): Response
    {
        $biblioteka = new Biblioteka();

        $czytelnikForm = $this->createForm(CzytelnikType::class);
        $czytelnikForm->handleRequest($request);
        if($czytelnikForm->isSubmitted() && $czytelnikForm->isValid()) {
            $czytelnikData = $czytelnikForm->getData();
            $insertResult = $biblioteka->addCzytelnik($czytelnikData['numer_karty']);
            $statusString = pg_result_status($insertResult);
            $queryErrors = pg_result_error($insertResult);
            if($statusString == 1) {
                return $this->redirectToRoute('app_czytelnik');
            } else {
                $czytelnikForm->addError(
                    new FormError($queryErrors)
                );
            }
        }
        return $this->render('czytelnik/dodaj.html.twig', [
            'czytelnik_form' => $czytelnikForm->createView(),
        ]);
    }


    /**
     * @Route("/czytelnik/{numerKarty}", name="czytelnik_details")
     */
    public function czytelnikDetails($numerKarty): Response
    {
        $biblioteka = new Biblioteka();
        $czytelnikRecord = $biblioteka->getCzytelnik($numerKarty);
        if(is_null($czytelnikRecord)) {
            throw new NotFoundHttpException('Czytelnik nie istnieje');
        }

        $aktualnyBan = ($czytelnikRecord['czy_ban_na_wypozyczenia'] == 't');
        $doZwrotu = $biblioteka->zalegleWypozyczeniaCzytelnika($numerKarty);
        $historiaWypozyczen = $biblioteka->zwroconeWypozyczeniaCzytelnika($numerKarty);
        return $this->render('czytelnik/details.html.twig', [
            'czy_ban' => $aktualnyBan,
            'numer_karty' => $numerKarty,
            'do_zwrotu' => $doZwrotu,
            'historia_wypozyczen'=>$historiaWypozyczen
        ]);
    }

    /**
     * @Route("/czytelnik/{numerKarty}/banuj", name="czytelik_banuj")
     */
    public function czytelnikBanuj($numerKarty): Response
    {
        $biblioteka = new Biblioteka();
        $czytelnikRecord = $biblioteka->getCzytelnik($numerKarty);
        if(is_null($czytelnikRecord)) {
            throw new NotFoundHttpException('Czytelnik nie istnieje');
        }

        $biblioteka->banujCzytelnika($numerKarty);

        return $this->redirectToRoute('czytelnik_details', [
            'numerKarty'=>$numerKarty
        ]);
    }

    /**
     * @Route("/czytelnik/{numerKarty}/odbanuj", name="czytelik_odbanuj")
     */
    public function czytelnikOdbanuj($numerKarty): Response
    {
        $biblioteka = new Biblioteka();
        $czytelnikRecord = $biblioteka->getCzytelnik($numerKarty);
        if(is_null($czytelnikRecord)) {
            throw new NotFoundHttpException('Czytelnik nie istnieje');
        }

        $biblioteka->odbanujCzytelnika($numerKarty);

        return $this->redirectToRoute('czytelnik_details', [
            'numerKarty'=>$numerKarty
        ]);
    }
}
