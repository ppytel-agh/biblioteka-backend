--DML
-- debiutujący autor
INSERT INTO autor (imie, nazwisko) VALUES ($1, $2);

-- nowa pozycja książkowa
INSERT INTO pozycja (autor_id, isbn, tytul, data_wydania) VALUES ($1, $2, $3, $4);

--rejestracja czytelnika
INSERT INTO czytelnik (numer_karty) VALUES ($1);

--rejestracja egzemplarza
INSERT INTO egzemplarz (numer, pozycja_id, data_nabycia) VALUES ($1, $2, $3);

--ban
UPDATE czytelnik SET czy_ban_na_wypozyczenia = TRUE WHERE numer_karty = $1;

--unban
UPDATE czytelnik SET czy_ban_na_wypozyczenia = FALSE WHERE numer_karty = $1;

--wypożyczenie
--czytelnik nie może mieć bana
--egzemplarz nie może być aktualnie wypożyczony
INSERT INTO wypozyczenie (egzemplarz_id, czytelnik_id, data_wypozyczenia) VALUES ($1, $2, $3);

--zwrot
--nie można nadpisać już zwróconego
UPDATE wypozyczenie SET data_zwrotu = $2 WHERE id = $1;

--KWERENDY

--autorzy
SELECT * FROM autor;

--dane autora
SELECT * FROM autor WHERE id = $1;

--pozycje autora
SELECT * FROM pozycja WHERE autor_id = $1;

--pozycja
SELECT * FROM pozycja WHERE isbn = $1;

--egzemplarze pozycji
SELECT * FROM egzemplarz WHERE pozycja_id = $1;

--czytelnicy
SELECT * FROM czytelnik

--dane wypożyczenia
SELECT * FROM wypozyczenie WHERE id = $1;

--czytelnik
SELECT * FROM czytelnik WHERE numer_karty = $1;

--niezwrócone egzemplarze
SELECT * FROM do_zwrotu;

--zaległe wypożyczenia czytelnika
SELECT * FROM do_zwrotu WHERE numer_karty = $1;

--zwrócone wypożyczenia czytelnika
SELECT * FROM wypozyczenie WHERE czytelnik_id = $1 AND data_zwrotu IS NOT NULL;

--dostępne tytuły
SELECT * FROM dostepne_tytuly;

--najbardziej popularni autorzy
SELECT * FROM popularni_autorzy ORDER BY l_wypozyczen DESC;

--dane egzemplarza
SELECT numer, tytul, (imie || \' \' || nazwisko) AS autor FROM egzemplarz E JOIN pozycja P ON E.pozycja_id = P.isbn JOIN autor A ON P.autor_id = A.id WHERE numer = $1;


--historia wypożyczeń
SELECT * FROM wypozyczenie WHERE egzemplarz_id = $1 AND data_zwrotu IS NOT NULL ORDER BY data_zwrotu DESC;

--aktualne wypożyczenie
SELECT * FROM wypozyczenie WHERE egzemplarz_id = $1 AND data_zwrotu IS NULL