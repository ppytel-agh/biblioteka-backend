<?php

namespace App\Controller;

use App\Database\Biblioteka;
use App\Form\EgzemplarzType;
use App\Form\WypozyczenieType;
use App\Form\ZamowienieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EgzemplarzController extends AbstractController
{
    /**
     * @Route("/egzemplarze", name="app_egzemplarze")
     */
    public function egzemplarze(): Response
    {
        $biblioteka = new Biblioteka();
        $dostepneTytuly = $biblioteka->dostepneTytuly();
        $niezwroconeEgzemplarze = $biblioteka->wszystkieNiezwrocone();
        return $this->render('egzemplarz/index.html.twig', [
            'dostepne_tytuly' => $dostepneTytuly,
            'niezwrocone' => $niezwroconeEgzemplarze,
        ]);
    }

    /**
     * @Route("/egzemplarze/pozycja/{pozycjaISBN}", name="egzemplarze")
     */
    public function egzemplarzePozycji($pozycjaISBN): Response
    {
        $biblioteka = new Biblioteka();
        $pozycjaDetails = $biblioteka->getPozycjaDetails($pozycjaISBN);
        if($pozycjaDetails == null) {
            throw new NotFoundHttpException('pozycja nie istnieje');
        }
        $egzemplarze = $biblioteka->getEgzemplarze($pozycjaISBN);
        return $this->render('egzemplarz/pozycja.html.twig', [
            'pozycja' => $pozycjaDetails,
            'egzemplarze' => $egzemplarze,
        ]);
    }

    /**
     * @Route("/egzemplarze/pozycja/{pozycjaISBN}/rejestracja", name="egzemplarz_rejetracja")
     */
    public function rejestracja($pozycjaISBN, Request $request) {
        $biblioteka = new Biblioteka();
        $pozycjaDetails = $biblioteka->getPozycjaDetails($pozycjaISBN);
        if($pozycjaDetails == null) {
            throw new NotFoundHttpException('pozycja nie istnieje');
        }

        $egzemplarzForm = $this->createForm(EgzemplarzType::class);
        $egzemplarzForm->handleRequest($request);
        if($egzemplarzForm->isSubmitted() && $egzemplarzForm->isValid()) {
            $formData = $egzemplarzForm->getData();
            $numer = $formData['numer'];
            $dataNabycia = $formData['data_nabycia']->format('Y-m-d');
            $insertResult = $biblioteka->addEgzemplarz($numer, $pozycjaISBN, $dataNabycia);
            $statusString = pg_result_status($insertResult);
            $queryErrors = pg_result_error($insertResult);
            if($statusString == 1) {
                return $this->redirectToRoute('egzemplarze', [
                    'pozycjaISBN'=>$pozycjaISBN
                ]);
            } else {
                $egzemplarzForm->addError(
                    new FormError($queryErrors)
                );
            }
        }
        return $this->render('egzemplarz/rejestracja.html.twig', [
            'pozycja' => $pozycjaDetails,
            'rejestracja_form'=>$egzemplarzForm->createView()
        ]);
    }

    /**
     * @Route("/egzemplarz/{egzemplarzId}", name="egzemplarz_details")
     */
    public function egzemparzSzczegoly($egzemplarzId) {
        //historia wypozyczen
        $biblioteka = new Biblioteka();
        $egzemplarzRecord = $biblioteka->getEgzemplarz($egzemplarzId);
        if($egzemplarzRecord == null) {
            throw new NotFoundHttpException('egzemplarz nie istnieje');
        }
        $aktualneWypozyczenie = $biblioteka->aktualneWypozyczenie($egzemplarzId);
        $historiaWypozyczen = $biblioteka->historiaWypozyczen($egzemplarzId);
        return $this->render('egzemplarz/details.html.twig',[
            'egzemplarz_id'=>$egzemplarzId,
            'aktualne' => $aktualneWypozyczenie,
            'historia' => $historiaWypozyczen,
            'egzemplarz' => $egzemplarzRecord
        ]);
    }

    /**
     * @Route("/egzemplarz/{egzemplarzId}/wypozycz", name="egzemplarz_wypozycz")
     */
    public function egzemplarzWypozycz($egzemplarzId, Request $request) {
        $biblioteka = new Biblioteka();
        $egzemplarzRecord = $biblioteka->getEgzemplarz($egzemplarzId);
        if($egzemplarzRecord == null) {
            throw new NotFoundHttpException('egzemplarz nie istnieje');
        }

        $wypozyczenieForm = $this->createForm(WypozyczenieType::class);
        $wypozyczenieForm->handleRequest($request);
        if($wypozyczenieForm->isSubmitted() && $wypozyczenieForm->isValid()) {
            $wypozyczenieData = $wypozyczenieForm->getData();
            $dataWypozyczenia = $wypozyczenieData['data_wypozyczenia']->format('Y-m-d');
            $numerKartyCzytelnika = $wypozyczenieData['karta_czytelnika'];
            $insertResult = $biblioteka->addWypozyczenie($egzemplarzId, $numerKartyCzytelnika, $dataWypozyczenia);
            $statusString = pg_result_status($insertResult);
            $queryErrors = pg_result_error($insertResult);
            if($statusString == 1) {
                return $this->redirectToRoute('egzemplarz_details', [
                    'egzemplarzId' => $egzemplarzId
                ]);
            } else {
                $wypozyczenieForm->addError(
                    new FormError($queryErrors)
                );
            }
        }
        return $this->render('egzemplarz/wypozyczenie.html.twig',[
            'numer_egzemplarza'=>$egzemplarzId,
            'wypozyczenie_form' => $wypozyczenieForm->createView()
        ]);
    }
}
