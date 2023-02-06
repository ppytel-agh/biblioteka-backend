<?php

namespace App\Database;

class Generator
{
    public function getRandomDigit() {
        $digit = chr(rand(ord('0'), ord('9')));
        return $digit;
    }

    public function getRandomCapitalLetter() {
        $capitalLetter = chr(rand(ord('A'), ord('Z')));
        return $capitalLetter;
    }

    public function getRandomSmallLetter() {
        $capitalLetter = chr(rand(ord('a'), ord('z')));
        return $capitalLetter;
    }

    public function getRandomDigitString($length) {
        $digitString = '';
        for($i = 0; $i < $length; $i++) {
            $digitString .= $this->getRandomDigit();
        }
        return $digitString;
    }

    public function getRandomCapitalLetterString($length) {
        $letterString = '';
        for($i = 0; $i < $length; $i++) {
            $letterString .= $this->getRandomCapitalLetter();
        }
        return $letterString;
    }

    public function getNumerKarty() {
        return $this->getRandomCapitalLetterString(2) . $this->getRandomDigitString(3);
    }

    public function getNumerEgzemplarza() {
        return $this->getRandomCapitalLetterString(3) . $this->getRandomDigitString(4);
    }
}