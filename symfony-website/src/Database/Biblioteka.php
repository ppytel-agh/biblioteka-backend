<?php

namespace App\Database;

class AutorInfo {
    public $imie;
    public $nazwisko;
}

class Biblioteka
{
    public function __construct()
    {
        $ciphering = new Ciphering();
        $hostname = $_ENV['DB_HOSTNAME'];
        $user = $_ENV['DB_USER'];
        $pass = $ciphering->decrypt($_ENV['DB_PASS_ENC']);
        $dbname = $_ENV['DB_NAME'];
        $connectionString = 'host='.$hostname.' user='.$user.' password='.$pass. ' dbname=' . $dbname;

        $this->connection = pg_connect($connectionString);

        pg_query($this->connection, 'SET search_path TO biblioteka;');
    }

    public function getResultErrorDetails($queryResult) {
        return [
            'PGSQL_DIAG_SEVERITY' => pg_result_error_field($queryResult, PGSQL_DIAG_SEVERITY),
            ' PGSQL_DIAG_SQLSTATE' => pg_result_error_field($queryResult,  PGSQL_DIAG_SQLSTATE),
            'PGSQL_DIAG_MESSAGE_PRIMARY' => pg_result_error_field($queryResult, PGSQL_DIAG_MESSAGE_PRIMARY),
            'PGSQL_DIAG_MESSAGE_DETAIL' => pg_result_error_field($queryResult, PGSQL_DIAG_MESSAGE_DETAIL),
            ' PGSQL_DIAG_MESSAGE_HINT' => pg_result_error_field($queryResult,  PGSQL_DIAG_MESSAGE_HINT),
            'PGSQL_DIAG_STATEMENT_POSITION' => pg_result_error_field($queryResult, PGSQL_DIAG_STATEMENT_POSITION),
            ' PGSQL_DIAG_INTERNAL_POSITION' => pg_result_error_field($queryResult,  PGSQL_DIAG_INTERNAL_POSITION),
            ' PGSQL_DIAG_INTERNAL_QUERY ' => pg_result_error_field($queryResult,  PGSQL_DIAG_INTERNAL_QUERY ),
            ' PGSQL_DIAG_CONTEXT' => pg_result_error_field($queryResult,  PGSQL_DIAG_CONTEXT),
            'PGSQL_DIAG_SOURCE_FILE' => pg_result_error_field($queryResult, PGSQL_DIAG_SOURCE_FILE),
            'PGSQL_DIAG_SOURCE_LINE ' => pg_result_error_field($queryResult, PGSQL_DIAG_SOURCE_LINE ),
            'PGSQL_DIAG_SOURCE_FUNCTION' => pg_result_error_field($queryResult,  PGSQL_DIAG_SOURCE_FUNCTION),
        ];
    }

    public function getAuthors() {
        $query = 'SELECT * FROM autor;';
        $getAuthorsResult = pg_query($this->connection, $query);
        $authorsArray = pg_fetch_all($getAuthorsResult);
        return $authorsArray;
    }

    public function addAuthor($firstName, $lastName) {
        $query = 'INSERT INTO autor (imie, nazwisko) VALUES ($1, $2);';
        $queryParams = [
            $firstName,
            $lastName,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function getAuthorInfo($authorId): ?AutorInfo
    {
        $query = 'SELECT * FROM autor WHERE id = $1;';
        $queryParams = [
            $authorId
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        if (isset($resultArray[0])) {
            $autor = new AutorInfo();
            $autor->imie = $resultArray[0]['imie'];
            $autor->nazwisko = $resultArray[0]['nazwisko'];
            return $autor;
        } else {
            return null;
        }
    }

    public function getAuthorBooks($authorId) {
        $query = 'SELECT * FROM pozycja WHERE autor_id = $1;';
        $queryParams = [
            $authorId
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function getPozycjaDetails($pozycjaISBN) {
        $query = 'SELECT * FROM pozycja WHERE isbn = $1;';
        $queryParams = [
            $pozycjaISBN
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        if (isset($resultArray[0])) {
            return $resultArray[0];
        } else {
            return null;
        }
    }

    public function addPozycja($autorId, $isbn, $tytul, $dataWydania) {
        $query = 'INSERT INTO pozycja (autor_id, isbn, tytul, data_wydania) VALUES ($1, $2, $3, $4);';
        $queryParams = [
            $autorId,
            $isbn,
            $tytul,
            $dataWydania,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function getEgzemplarze($pozycjaISBN) {
        $query = 'SELECT * FROM egzemplarz WHERE pozycja_id = $1;';
        $queryParams = [
            $pozycjaISBN
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function addEgzemplarz($numer, $pozycjaISBN, $dataNabycia) {
        $query = 'INSERT INTO egzemplarz (numer, pozycja_id, data_nabycia) VALUES ($1, $2, $3);';
        $queryParams = [
            $numer,
            $pozycjaISBN,
            $dataNabycia,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function getCzytelnicy() {
        $query = 'SELECT * FROM czytelnik;';
        $queryResult = pg_query($this->connection, $query);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function addCzytelnik($numerKarty) {
        $query = 'INSERT INTO czytelnik (numer_karty) VALUES ($1);';
        $queryParams = [
            $numerKarty,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function banujCzytelnika($numerKarty) {
        $query = 'UPDATE czytelnik SET czy_ban_na_wypozyczenia = TRUE WHERE numer_karty = $1;';
        $queryParams = [
            $numerKarty,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function odbanujCzytelnika($numerKarty) {
        $query = 'UPDATE czytelnik SET czy_ban_na_wypozyczenia = FALSE WHERE numer_karty = $1;';
        $queryParams = [
            $numerKarty,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function getAktualneWypozyczenia() {

    }

    public function addWypozyczenie($egzemplarzId, $numerKartyCzytelnika, $dataWypozyczenia) {
        $query = 'INSERT INTO wypozyczenie (egzemplarz_id, czytelnik_id, data_wypozyczenia) VALUES ($1, $2, $3);';
        $queryParams = [
            $egzemplarzId,
            $numerKartyCzytelnika,
            $dataWypozyczenia
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function zwrotWypozyczenia($wypozyczenieId, $dataZwrotu) {
        $query = 'UPDATE wypozyczenie SET data_zwrotu = $2 WHERE id = $1;';
        $queryParams = [
            $wypozyczenieId,
            $dataZwrotu,
        ];
        pg_send_query_params($this->connection, $query, $queryParams);
        $queryResult = pg_get_result($this->connection);
        return $queryResult;
    }

    public function getWypozyczenie($wypozyczenieId) {
        $query = 'SELECT * FROM wypozyczenie WHERE id = $1;';
        $queryParams = [
            $wypozyczenieId,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        if (isset($resultArray[0])) {
            $record = $resultArray[0];
            return $record;
        } else {
            return null;
        }
    }

    public function getCzytelnik($numerKarty) {
        $query = 'SELECT * FROM czytelnik WHERE numer_karty = $1;';
        $queryParams = [
            $numerKarty,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        if (isset($resultArray[0])) {
            $czytelnikRecord = $resultArray[0];
            return $czytelnikRecord;
        } else {
            return null;
        }
    }

    public function wszystkieNiezwrocone() {
        $query = 'SELECT * FROM do_zwrotu;';
        $queryResult = pg_query($this->connection, $query);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function zalegleWypozyczeniaCzytelnika($numerKarty) {
        $query = 'SELECT * FROM do_zwrotu WHERE numer_karty = $1;';
        $queryParams = [
            $numerKarty,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function zwroconeWypozyczeniaCzytelnika($numerKarty) {
        $query = 'SELECT * FROM wypozyczenie WHERE czytelnik_id = $1 AND data_zwrotu IS NOT NULL;';
        $queryParams = [
            $numerKarty,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function dostepneTytuly() {
        $query = 'SELECT * FROM dostepne_tytuly;';
        $queryResult = pg_query($this->connection, $query);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function popularniAutorzy() {
        $query = 'SELECT * FROM popularni_autorzy ORDER BY l_wypozyczen DESC;';
        $queryResult = pg_query($this->connection, $query);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function getEgzemplarz($numer) {
        $query = 'SELECT numer, tytul, (imie || \' \' || nazwisko) AS autor FROM egzemplarz E JOIN pozycja P ON E.pozycja_id = P.isbn JOIN autor A ON P.autor_id = A.id WHERE numer = $1;';
        $queryParams = [
            $numer,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        if (isset($resultArray[0])) {
            $record = $resultArray[0];
            return $record;
        } else {
            return null;
        }
    }

    public function historiaWypozyczen($egzemplarzId) {
        $query = 'SELECT * FROM wypozyczenie WHERE egzemplarz_id = $1 AND data_zwrotu IS NOT NULL ORDER BY data_zwrotu DESC;';
        $queryParams = [
            $egzemplarzId,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        return $resultArray;
    }

    public function aktualneWypozyczenie($egzemplarzId) {
        $query = 'SELECT * FROM wypozyczenie WHERE egzemplarz_id = $1 AND data_zwrotu IS NULL;';
        $queryParams = [
            $egzemplarzId,
        ];
        $queryResult = pg_query_params($this->connection, $query, $queryParams);
        $resultArray = pg_fetch_all($queryResult);
        if (isset($resultArray[0])) {
            $record = $resultArray[0];
            return $record;
        } else {
            return null;
        }
    }

    private $connection;
}