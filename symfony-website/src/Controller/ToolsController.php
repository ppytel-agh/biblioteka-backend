<?php

namespace App\Controller;

use App\Database\Ciphering;
use App\Database\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tools")
 */
class ToolsController extends AbstractController
{
    /**
     * @Route("/", name="tools_dashboard")
     */
    public function index(): Response
    {
        return $this->render('tools/index.html.twig', [
            'controller_name' => 'ToolsController',
        ]);
    }

    /**
     * @Route("/encrypt/{phrase}", name="encrypt")
     */
    public function encrypt($phrase) {
        $ciphering = new Ciphering();
        $encrypted = $ciphering->encrypt($phrase);
        return $this->render('default/encrypt.html.twig', [
            'encrypted' => $encrypted
        ]);
    }

    /**
     * @Route("/decrypt/{phrase}", name="decrypt")
     */
    public function decrypt($phrase) {
        $ciphering = new Ciphering();
        $encrypted = $ciphering->decrypt($phrase);
        return $this->render('default/encrypt.html.twig', [
            'encrypted' => $encrypted
        ]);
    }

    /**
     * @Route("/digit-string/{length}", name="digit_string")
     */
    public function generateDigitString($length) {
        $generator = new Generator();
        $isbn = $generator->getRandomDigitString($length);
        return $this->render('tools/generator.html.twig', [
            'title'=>'ISBN-'.$length,
            'generated_value'=>$isbn,
        ]);
    }

    /**
     * @Route("/numer-karty", name="numer_karty")
     */
    public function generateNumerKarty() {
        $generator = new Generator();
        $numerKarty = $generator->getNumerKarty();
        return $this->render('tools/generator.html.twig', [
            'title'=>'Numer karty',
            'generated_value'=>$numerKarty,
        ]);
    }

    /**
     * @Route("/numer-egzemplarza", name="numer_egzemplarza")
     */
    public function generateNumerEgzemplarza() {
        $generator = new Generator();
        $egzemplarz = $generator->getNumerEgzemplarza();
        return $this->render('tools/generator.html.twig', [
            'title'=>'Numer egzemplarza',
            'generated_value'=>$egzemplarz,
        ]);
    }
}
