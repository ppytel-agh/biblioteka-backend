--niezwrócone egzemplarze
CREATE VIEW do_zwrotu(wypozyczenie_id, numer_karty, tytul, egzemplarz, data_wypozyczenia)
AS
SELECT W.id, numer_karty, tytul, E.id, data_wypozyczenia FROM wypozyczenie W
	JOIN czytelnik C ON W.czytelnik_id = C.numer_karty
	JOIN egzemplarz E ON W.egzemplarz_id = E.id
	JOIN pozycja P ON E.pozycja_id = P.ISBN
	WHERE W.data_zwrotu IS NULL;

--dostępne tytuły
--egzemplarze nigdy nie wypożyczone lub już zwrócone
CREATE VIEW dostepne_tytuly(ISBN, tytul, autor, l_egzemplarzy)
AS
WITH dostepne_egzemplarze(ISBN, l_egzemplarzy) AS
(
	SELECT ISBN, COUNT(*) FROM egzemplarz E 
	LEFT JOIN wypozyczenie W ON E.id = W.egzemplarz_id
	JOIN pozycja P ON E.pozycja_id = P.ISBN 
	WHERE W.data_wypozyczenia IS NULL OR W.data_zwrotu IS NOT NULL
	GROUP BY P.ISBN
)
SELECT E.ISBN, tytul, imie || nazwisko, l_egzemplarzy
	FROM dostepne_egzemplarze E
	JOIN pozycja P ON E.ISBN = P.ISBN
	JOIN autor A ON P.autor_id = A.id;

--dzieła autora
CREATE VIEW produktywni_autorzy(autor, l_pozycji)
AS
WITH liczba_tytulow_autora(id_autora, l_pozycji) AS
(
	SELECT A.id, count(*) FROM autor A
		JOIN pozycja P ON A.id = P.autor_id
		GROUP BY A.id
)
SELECT imie || nazwisko, l_pozycji FROM liczba_tytulow_autora LTA
	JOIN AUTOR A ON A.id = LTA.id_autora
	ORDER BY l_pozycji DESC;
	
CREATE VIEW popularni_autorzy(id, autor, l_wypozyczen)
AS
WITH liczba_wypozyczen_autora(id_autora, l_wypozyczen) AS
(
	SELECT A.id, count(*) FROM autor A
		JOIN pozycja P ON A.id = P.autor_id
		JOIN egzemplarz E ON P.isbn = E.pozycja_id
		JOIN wypozyczenie W ON E.id = W.egzemplarz_id
		GROUP BY A.id
)
SELECT A.id, imie || nazwisko, l_wypozyczen FROM liczba_wypozyczen_autora LWA
	JOIN AUTOR A ON A.id = LWA.id_autora
	ORDER BY l_wypozyczen DESC;
	
--historia wypożyczeń
SELECT * FROM wypozyczenie WHERE egzemplarz_id = $1 AND data_zwrotu IS NULL
UNION
SELECT * FROM wypozyczenie WHERE egzemplarz_id = $1 AND data_zwrotu IS NOT NULL ORDER BY data_zwrotu DESC;