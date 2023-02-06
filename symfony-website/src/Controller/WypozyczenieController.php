<?php

namespace App\Controller;

use App\Database\Biblioteka;
use App\Form\ZwrotType;
use PHPUnit\Runner\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WypozyczenieController extends AbstractController
{

    /**
     * @Route("/wypozyczenie/{wypozyczenieId}/zwrot", name="wypozyczenie_zwrot")
     */
    public function wypozyczenieZwrot($wypozyczenieId,Request $request): Response
    {
        $biblioteka = new Biblioteka();
        $wypozyczenie = $biblioteka->getWypozyczenie($wypozyczenieId);
        if(is_null($wypozyczenie)) {
            throw new Exception('Brak wypożyczenia o poddanym id');
        }

        $zwrotForm = $this->createForm(ZwrotType::class);
        $zwrotForm->handleRequest($request);
        if($zwrotForm->isSubmitted() && $zwrotForm->isValid()) {
            $zwrotData = $zwrotForm->getData();
            $dataZwrotu = $zwrotData['data_zwrotu']->format('Y-m-d');
            $updateResult = $biblioteka->zwrotWypozyczenia($wypozyczenieId, $dataZwrotu);
            $statusString = pg_result_status($updateResult);
            $queryErrors = pg_result_error($updateResult);
            if($statusString == 1) {
                return $this->redirectToRoute('czytelnik_details', [
                    'numerKarty' => $wypozyczenie['czytelnik_id']
                ]);
            } else {
                $zwrotForm->addError(
                    new FormError($queryErrors)
                );
            }

        }
        return $this->render('wypozyczenie/zwrot.html.twig', [
            'zwrot_form' => $zwrotForm->createView()
        ]);
    }
}
