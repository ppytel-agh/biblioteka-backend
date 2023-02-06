-- debiutujący autor
INSERT INTO autor (imie, nazwisko) VALEUS ('Doc', 'Oak');

-- nowa pozycja książkowa
INSERT INTO pozycja VALUES ('12345', 'labirynt fauna', 1, '2022-01-15');

--rejestracja czytelnika
INSERT INTO czytelnik (numer_karty) VALUES ('AB123');

--nabycie egzemplarza
INSERT INTO egzemplarz (pozycja_id, data_nabycia) VALUES ('12345', '2022-01-15');

--wypożyczenie
--czytelnik nie może mieć bana
--egzemplarz nie może być aktualnie wypożyczony
INSERT INTO wypozyczenie (egzemplarz_id, czytelnik_id, data_wypozyczenia) VALUES ('12345', 'AB123', '2022-01-16');

--zwrot
--nie można nadpisać już zwróconego
UPDATE wypozyczenie SET data_zwrotu = '2022-01-17' WHERE id = 1;

--ban
UPDATE czytelnik SET czy_aktualnie_ban = TRUE WHERE numer_karty = 'ABC123';

--unban
UPDATE czytelnik SET czy_aktualnie_ban = FALSE WHERE numer_karty = 'ABC123';

--derejestracja czytelnika
--musi dokonać wszystkich zwrotów
DELETE FROM wypozyczenie WHERE numer_karty = 'ABC123';
DELETE FROM czytelnik WHERE numer_karty = 'ABC123';