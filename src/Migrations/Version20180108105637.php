<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180108105637 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE author (id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE authors_books (author_id INTEGER NOT NULL, book_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_id))');
        $this->addSql('CREATE INDEX IDX_2DFDA3CBF675F31B ON authors_books (author_id)');
        $this->addSql('CREATE INDEX IDX_2DFDA3CB16A2B381 ON authors_books (book_id)');
        $this->addSql('CREATE TABLE authors_bookseries (author_id INTEGER NOT NULL, book_serie_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_serie_id))');
        $this->addSql('CREATE INDEX IDX_9C6BFDB1F675F31B ON authors_bookseries (author_id)');
        $this->addSql('CREATE INDEX IDX_9C6BFDB1C0BC9EE4 ON authors_bookseries (book_serie_id)');
        $this->addSql('CREATE TABLE book (id INTEGER NOT NULL, bookserie_id INTEGER DEFAULT NULL, isbn VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL, publisher VARCHAR(255) NOT NULL, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL, annotation VARCHAR(255) NOT NULL, rating DOUBLE PRECISION NOT NULL, times_borrowed INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CBE5A3313E2B4156 ON book (bookserie_id)');
        $this->addSql('CREATE TABLE books_genres (book_id INTEGER NOT NULL, genre_id INTEGER NOT NULL, PRIMARY KEY(book_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_6C215D1A16A2B381 ON books_genres (book_id)');
        $this->addSql('CREATE INDEX IDX_6C215D1A4296D31F ON books_genres (genre_id)');
        $this->addSql('CREATE TABLE book_reservation (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, reader_id INTEGER DEFAULT NULL, date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL, fine DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_103F062916A2B381 ON book_reservation (book_id)');
        $this->addSql('CREATE INDEX IDX_103F06291717D737 ON book_reservation (reader_id)');
        $this->addSql('CREATE TABLE book_serie (id INTEGER NOT NULL, part INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE comment (id INTEGER NOT NULL, published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL, book_id INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9474526C16A2B381 ON comment (book_id)');
        $this->addSql('CREATE TABLE genre (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE notification (id INTEGER NOT NULL, receiver_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CACD53EDB6 ON notification (receiver_id)');
        $this->addSql('CREATE TABLE user (id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL, photo VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE authors_books');
        $this->addSql('DROP TABLE authors_bookseries');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE books_genres');
        $this->addSql('DROP TABLE book_reservation');
        $this->addSql('DROP TABLE book_serie');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE user');
    }
}
