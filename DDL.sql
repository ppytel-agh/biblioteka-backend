--DROP SCHEMA biblioteka CASCADE;

CREATE SCHEMA biblioteka;

SET search_path TO biblioteka;

--funkcje wbudowane

CREATE OR REPLACE FUNCTION format_egzemplarza(numer_egzemplarza TEXT) RETURNS BOOL AS $$
	BEGIN
		IF numer_egzemplarza ~ '^[A-Z]{3}\d{4}$' THEN
			RETURN TRUE;
		ELSE			
			RAISE WARNING 'numer karty musi składać się z trzech małych liter i czterech cyfr';
			RETURN FALSE;
		END IF;
	END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION format_karty_czytelnika(numer_karty TEXT) RETURNS BOOL AS $$
	BEGIN
		IF numer_karty ~ '^[A-Z]{2}\d{3}$' THEN
			RETURN TRUE;
		ELSE			
			RAISE WARNING 'numer karty musi składać się z dwóch dużych liter i trzech cyfr';
			RETURN FALSE;
		END IF;
	END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION poprawny_ISBN(ISBN TEXT) RETURNS BOOL AS $$
	BEGIN
		IF ISBN ~ '^\d{10}$|^\d{13}$' THEN
			RETURN TRUE;
		ELSE			
			RAISE WARNING 'ISBN musi składać się z 10 lub 13 cyfr';
			RETURN FALSE;
		END IF;
	END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION polskie_wyrazenie(wyrazenie TEXT) RETURNS BOOL AS $$
	BEGIN
		IF wyrazenie ~ '^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+$' THEN
			RETURN TRUE;
		ELSE			
			RAISE WARNING 'wyrażenie powinno zawierać wyłącznie polskie znaki i spacje';
			RETURN FALSE;
		END IF;
	END;
$$ LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION okres_dzialalnosci(sprawdzana_data DATE) RETURNS BOOL AS $$
	DECLARE
		data_powstania_biblioteki CONSTANT DATE := '2019-02-15';
	BEGIN
		IF sprawdzana_data < data_powstania_biblioteki THEN
			RAISE WARNING 'data wcześniejsza niż powstanie biblioteki';
			RETURN FALSE;
		END IF;
		IF sprawdzana_data > CURRENT_DATE THEN
			RAISE WARNING 'data z przyszłości';
			RETURN FALSE;
		END IF;
		RETURN TRUE;
	END;
$$ LANGUAGE 'plpgsql';

--tabele

CREATE TABLE autor (
	id SERIAL PRIMARY KEY,
	imie VARCHAR(64) NOT NULL CONSTRAINT poprawne_imie CHECK (polskie_wyrazenie(imie)),
	nazwisko VARCHAR(64) NOT NULL CONSTRAINT poprawne_nazwisko CHECK (polskie_wyrazenie(nazwisko))
);

CREATE TABLE czytelnik (
	numer_karty	CHAR(5) PRIMARY KEY,
	czy_ban_na_wypozyczenia BOOL DEFAULT FALSE,
	CONSTRAINT poprawny_format_karty CHECK(format_karty_czytelnika(numer_karty))
);

CREATE TABLE pozycja (
	ISBN VARCHAR(16) PRIMARY KEY CONSTRAINT dlugosc_ISBN CHECK(poprawny_ISBN(ISBN)),
	tytul VARCHAR(128) NOT NULL, --może zawierać różne znaki interpunkcyjne
	autor_id INTEGER REFERENCES autor(id),
	data_wydania DATE NOT NULL --może zostać ogłoszona przed premierą
);

CREATE TABLE egzemplarz (
	numer CHAR(7) PRIMARY KEY,
	pozycja_id VARCHAR(16) REFERENCES pozycja(ISBN),
	data_nabycia DATE NOT NULL CONSTRAINT nabyte_w_trakcie_dzialania_biblioteki CHECK(okres_dzialalnosci(data_nabycia)),
	CONSTRAINT poprawny_format_egzemplarza CHECK(format_egzemplarza(numer))
);

CREATE TABLE wypozyczenie (
	id SERIAL PRIMARY KEY,
	egzemplarz_id CHAR(7) REFERENCES egzemplarz(numer),
	czytelnik_id CHAR(5) REFERENCES czytelnik(numer_karty),
	data_wypozyczenia DATE NOT NULL CONSTRAINT wypozyczone_w_trakcie_dzialania_biblioteki CHECK(okres_dzialalnosci(data_wypozyczenia)),
	data_zwrotu DATE CONSTRAINT zwrocone_w_trakcie_dzialania_biblioteki CHECK(okres_dzialalnosci(data_zwrotu))
);

--wyzwalacze

CREATE OR REPLACE FUNCTION sprawdz_czy_mozna_wypozyczyc() RETURNS TRIGGER AS $$
    BEGIN
		IF (SELECT czy_ban_na_wypozyczenia FROM czytelnik WHERE numer_karty = NEW.czytelnik_id) THEN
			RAISE EXCEPTION 'Czytelnik nie może w tym momencie wypożyczać książek';
		END IF;
		IF (SELECT COUNT(*) FROM wypozyczenie WHERE egzemplarz_id = NEW.egzemplarz_id AND data_zwrotu IS NULL) > 0 THEN
			RAISE EXCEPTION 'Egzemplarz niedostępny';
		END IF;
        RETURN NEW;
    END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION waliduj_zwrot() RETURNS TRIGGER AS $$
    BEGIN
		IF OLD.data_zwrotu IS NOT NULL THEN
			RAISE EXCEPTION 'Nie można zwrócić pozycji dwa razy';
		END IF;
        RETURN NEW;
    END;
$$ LANGUAGE 'plpgsql';


CREATE TRIGGER odmowa_wypozyczenia
	BEFORE INSERT ON wypozyczenie
	FOR EACH ROW EXECUTE PROCEDURE sprawdz_czy_mozna_wypozyczyc();
	
CREATE TRIGGER walidacja_zwrotu
	BEFORE UPDATE ON wypozyczenie
	FOR EACH ROW EXECUTE PROCEDURE waliduj_zwrot();
	
--widoki

--niezwrócone egzemplarze
CREATE VIEW do_zwrotu(wypozyczenie_id, numer_karty, tytul, egzemplarz, data_wypozyczenia)
AS
SELECT W.id, numer_karty, tytul, E.numer, data_wypozyczenia FROM wypozyczenie W
	JOIN czytelnik C ON W.czytelnik_id = C.numer_karty
	JOIN egzemplarz E ON W.egzemplarz_id = E.numer
	JOIN pozycja P ON E.pozycja_id = P.ISBN
	WHERE W.data_zwrotu IS NULL;

--dostępne tytuły
--egzemplarze nigdy nie wypożyczone lub już zwrócone
CREATE OR REPLACE VIEW dostepne_tytuly(ISBN, tytul, autor, l_egzemplarzy)
AS
WITH dostepne_egzemplarze AS
(
	SELECT DISTINCT numer, pozycja_id FROM egzemplarz E 
	LEFT JOIN wypozyczenie W ON E.numer = W.egzemplarz_id
	WHERE W.data_wypozyczenia IS NULL OR W.data_zwrotu IS NOT NULL
), liczba_egzemplarzy(ISBN, l_egzemplarzy) AS
(
	SELECT ISBN, COUNT(*) FROM dostepne_egzemplarze DE
	JOIN pozycja P ON DE.pozycja_id = P.ISBN
	GROUP BY P.ISBN
)
SELECT LE.ISBN, tytul, imie || nazwisko, l_egzemplarzy
	FROM liczba_egzemplarzy LE
	JOIN pozycja P ON LE.ISBN = P.ISBN
	JOIN autor A ON P.autor_id = A.id;

--autorzy, których dzieła wypożyczono przynajmniej 10 razy
CREATE VIEW popularni_autorzy(id, autor, l_wypozyczen)
AS
WITH wypozyczeni_przynajmniej_10_razy(id_autora, l_wypozyczen) AS
(
	SELECT A.id, count(*) FROM autor A
		JOIN pozycja P ON A.id = P.autor_id
		JOIN egzemplarz E ON P.isbn = E.pozycja_id
		JOIN wypozyczenie W ON E.numer = W.egzemplarz_id
		GROUP BY A.id
		HAVING count(*) > 10
)
SELECT A.id, imie || nazwisko, l_wypozyczen FROM wypozyczeni_przynajmniej_10_razy LWA
	JOIN AUTOR A ON A.id = LWA.id_autora;
