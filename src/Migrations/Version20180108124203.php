<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180108124203 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_2DFDA3CBF675F31B');
        $this->addSql('DROP INDEX IDX_2DFDA3CB16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authors_books AS SELECT author_id, book_id FROM authors_books');
        $this->addSql('DROP TABLE authors_books');
        $this->addSql('CREATE TABLE authors_books (author_id INTEGER NOT NULL, book_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_id), CONSTRAINT FK_2DFDA3CBF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2DFDA3CB16A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authors_books (author_id, book_id) SELECT author_id, book_id FROM __temp__authors_books');
        $this->addSql('DROP TABLE __temp__authors_books');
        $this->addSql('CREATE INDEX IDX_2DFDA3CBF675F31B ON authors_books (author_id)');
        $this->addSql('CREATE INDEX IDX_2DFDA3CB16A2B381 ON authors_books (book_id)');
        $this->addSql('DROP INDEX IDX_9C6BFDB1F675F31B');
        $this->addSql('DROP INDEX IDX_9C6BFDB1C0BC9EE4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authors_bookseries AS SELECT author_id, book_serie_id FROM authors_bookseries');
        $this->addSql('DROP TABLE authors_bookseries');
        $this->addSql('CREATE TABLE authors_bookseries (author_id INTEGER NOT NULL, book_serie_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_serie_id), CONSTRAINT FK_9C6BFDB1F675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9C6BFDB1C0BC9EE4 FOREIGN KEY (book_serie_id) REFERENCES book_serie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authors_bookseries (author_id, book_serie_id) SELECT author_id, book_serie_id FROM __temp__authors_bookseries');
        $this->addSql('DROP TABLE __temp__authors_bookseries');
        $this->addSql('CREATE INDEX IDX_9C6BFDB1F675F31B ON authors_bookseries (author_id)');
        $this->addSql('CREATE INDEX IDX_9C6BFDB1C0BC9EE4 ON authors_bookseries (book_serie_id)');
        $this->addSql('DROP INDEX IDX_CBE5A3313E2B4156');
        $this->addSql('CREATE TEMPORARY TABLE __temp__book AS SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, rating, times_borrowed FROM book');
        $this->addSql('DROP TABLE book');
        $this->addSql('CREATE TABLE book (id INTEGER NOT NULL, bookserie_id INTEGER DEFAULT NULL, isbn VARCHAR(255) NOT NULL COLLATE BINARY, title VARCHAR(255) NOT NULL COLLATE BINARY, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, publisher VARCHAR(255) NOT NULL COLLATE BINARY, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL COLLATE BINARY, annotation VARCHAR(255) NOT NULL COLLATE BINARY, rating DOUBLE PRECISION NOT NULL, times_borrowed INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_CBE5A3313E2B4156 FOREIGN KEY (bookserie_id) REFERENCES book_serie (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO book (id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, rating, times_borrowed) SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, rating, times_borrowed FROM __temp__book');
        $this->addSql('DROP TABLE __temp__book');
        $this->addSql('CREATE INDEX IDX_CBE5A3313E2B4156 ON book (bookserie_id)');
        $this->addSql('DROP INDEX IDX_6C215D1A16A2B381');
        $this->addSql('DROP INDEX IDX_6C215D1A4296D31F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__books_genres AS SELECT book_id, genre_id FROM books_genres');
        $this->addSql('DROP TABLE books_genres');
        $this->addSql('CREATE TABLE books_genres (book_id INTEGER NOT NULL, genre_id INTEGER NOT NULL, PRIMARY KEY(book_id, genre_id), CONSTRAINT FK_6C215D1A16A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6C215D1A4296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO books_genres (book_id, genre_id) SELECT book_id, genre_id FROM __temp__books_genres');
        $this->addSql('DROP TABLE __temp__books_genres');
        $this->addSql('CREATE INDEX IDX_6C215D1A16A2B381 ON books_genres (book_id)');
        $this->addSql('CREATE INDEX IDX_6C215D1A4296D31F ON books_genres (genre_id)');
        $this->addSql('DROP INDEX IDX_103F062916A2B381');
        $this->addSql('DROP INDEX IDX_103F06291717D737');
        $this->addSql('CREATE TEMPORARY TABLE __temp__book_reservation AS SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM book_reservation');
        $this->addSql('DROP TABLE book_reservation');
        $this->addSql('CREATE TABLE book_reservation (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, reader_id INTEGER DEFAULT NULL, date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL COLLATE BINARY, fine DOUBLE PRECISION NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_103F062916A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_103F06291717D737 FOREIGN KEY (reader_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO book_reservation (id, book_id, reader_id, date_from, date_to, status, fine) SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM __temp__book_reservation');
        $this->addSql('DROP TABLE __temp__book_reservation');
        $this->addSql('CREATE INDEX IDX_103F062916A2B381 ON book_reservation (book_id)');
        $this->addSql('CREATE INDEX IDX_103F06291717D737 ON book_reservation (reader_id)');
        $this->addSql('DROP INDEX IDX_9474526CA76ED395');
        $this->addSql('DROP INDEX IDX_9474526C16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, book_id, user_id, published_at, content FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_9474526C16A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO comment (id, book_id, user_id, published_at, content) SELECT id, book_id, user_id, published_at, content FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('CREATE INDEX IDX_9474526C16A2B381 ON comment (book_id)');
        $this->addSql('DROP INDEX IDX_BF5476CACD53EDB6');
        $this->addSql('CREATE TEMPORARY TABLE __temp__notification AS SELECT id, receiver_id, title, content FROM notification');
        $this->addSql('DROP TABLE notification');
        $this->addSql('CREATE TABLE notification (id INTEGER NOT NULL, receiver_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, content VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_BF5476CACD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO notification (id, receiver_id, title, content) SELECT id, receiver_id, title, content FROM __temp__notification');
        $this->addSql('DROP TABLE __temp__notification');
        $this->addSql('CREATE INDEX IDX_BF5476CACD53EDB6 ON notification (receiver_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_2DFDA3CBF675F31B');
        $this->addSql('DROP INDEX IDX_2DFDA3CB16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authors_books AS SELECT author_id, book_id FROM authors_books');
        $this->addSql('DROP TABLE authors_books');
        $this->addSql('CREATE TABLE authors_books (author_id INTEGER NOT NULL, book_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_id))');
        $this->addSql('INSERT INTO authors_books (author_id, book_id) SELECT author_id, book_id FROM __temp__authors_books');
        $this->addSql('DROP TABLE __temp__authors_books');
        $this->addSql('CREATE INDEX IDX_2DFDA3CBF675F31B ON authors_books (author_id)');
        $this->addSql('CREATE INDEX IDX_2DFDA3CB16A2B381 ON authors_books (book_id)');
        $this->addSql('DROP INDEX IDX_9C6BFDB1F675F31B');
        $this->addSql('DROP INDEX IDX_9C6BFDB1C0BC9EE4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authors_bookseries AS SELECT author_id, book_serie_id FROM authors_bookseries');
        $this->addSql('DROP TABLE authors_bookseries');
        $this->addSql('CREATE TABLE authors_bookseries (author_id INTEGER NOT NULL, book_serie_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_serie_id))');
        $this->addSql('INSERT INTO authors_bookseries (author_id, book_serie_id) SELECT author_id, book_serie_id FROM __temp__authors_bookseries');
        $this->addSql('DROP TABLE __temp__authors_bookseries');
        $this->addSql('CREATE INDEX IDX_9C6BFDB1F675F31B ON authors_bookseries (author_id)');
        $this->addSql('CREATE INDEX IDX_9C6BFDB1C0BC9EE4 ON authors_bookseries (book_serie_id)');
        $this->addSql('DROP INDEX IDX_CBE5A3313E2B4156');
        $this->addSql('CREATE TEMPORARY TABLE __temp__book AS SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, rating, times_borrowed FROM book');
        $this->addSql('DROP TABLE book');
        $this->addSql('CREATE TABLE book (id INTEGER NOT NULL, bookserie_id INTEGER DEFAULT NULL, isbn VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL, publisher VARCHAR(255) NOT NULL, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL, annotation VARCHAR(255) NOT NULL, rating DOUBLE PRECISION NOT NULL, times_borrowed INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO book (id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, rating, times_borrowed) SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, rating, times_borrowed FROM __temp__book');
        $this->addSql('DROP TABLE __temp__book');
        $this->addSql('CREATE INDEX IDX_CBE5A3313E2B4156 ON book (bookserie_id)');
        $this->addSql('DROP INDEX IDX_103F062916A2B381');
        $this->addSql('DROP INDEX IDX_103F06291717D737');
        $this->addSql('CREATE TEMPORARY TABLE __temp__book_reservation AS SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM book_reservation');
        $this->addSql('DROP TABLE book_reservation');
        $this->addSql('CREATE TABLE book_reservation (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, reader_id INTEGER DEFAULT NULL, date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL, fine DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO book_reservation (id, book_id, reader_id, date_from, date_to, status, fine) SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM __temp__book_reservation');
        $this->addSql('DROP TABLE __temp__book_reservation');
        $this->addSql('CREATE INDEX IDX_103F062916A2B381 ON book_reservation (book_id)');
        $this->addSql('CREATE INDEX IDX_103F06291717D737 ON book_reservation (reader_id)');
        $this->addSql('DROP INDEX IDX_6C215D1A16A2B381');
        $this->addSql('DROP INDEX IDX_6C215D1A4296D31F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__books_genres AS SELECT book_id, genre_id FROM books_genres');
        $this->addSql('DROP TABLE books_genres');
        $this->addSql('CREATE TABLE books_genres (book_id INTEGER NOT NULL, genre_id INTEGER NOT NULL, PRIMARY KEY(book_id, genre_id))');
        $this->addSql('INSERT INTO books_genres (book_id, genre_id) SELECT book_id, genre_id FROM __temp__books_genres');
        $this->addSql('DROP TABLE __temp__books_genres');
        $this->addSql('CREATE INDEX IDX_6C215D1A16A2B381 ON books_genres (book_id)');
        $this->addSql('CREATE INDEX IDX_6C215D1A4296D31F ON books_genres (genre_id)');
        $this->addSql('DROP INDEX IDX_9474526C16A2B381');
        $this->addSql('DROP INDEX IDX_9474526CA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, book_id, user_id, published_at, content FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO comment (id, book_id, user_id, published_at, content) SELECT id, book_id, user_id, published_at, content FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526C16A2B381 ON comment (book_id)');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('DROP INDEX IDX_BF5476CACD53EDB6');
        $this->addSql('CREATE TEMPORARY TABLE __temp__notification AS SELECT id, receiver_id, title, content FROM notification');
        $this->addSql('DROP TABLE notification');
        $this->addSql('CREATE TABLE notification (id INTEGER NOT NULL, receiver_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO notification (id, receiver_id, title, content) SELECT id, receiver_id, title, content FROM __temp__notification');
        $this->addSql('DROP TABLE __temp__notification');
        $this->addSql('CREATE INDEX IDX_BF5476CACD53EDB6 ON notification (receiver_id)');
    }
}
