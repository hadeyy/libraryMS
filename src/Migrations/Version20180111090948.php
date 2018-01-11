<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180111090948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_E3EA0499A76ED395');
        $this->addSql('DROP INDEX IDX_E3EA049916A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_activities AS SELECT id, book_id, user_id, title, content, time FROM app_activities');
        $this->addSql('DROP TABLE app_activities');
        $this->addSql('CREATE TABLE app_activities (id INTEGER NOT NULL, book_id INTEGER UNSIGNED DEFAULT NULL, user_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, content VARCHAR(255) NOT NULL COLLATE BINARY, time DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_E3EA049916A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E3EA0499A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_activities (id, book_id, user_id, title, content, time) SELECT id, book_id, user_id, title, content, time FROM __temp__app_activities');
        $this->addSql('DROP TABLE __temp__app_activities');
        $this->addSql('CREATE INDEX IDX_E3EA0499A76ED395 ON app_activities (user_id)');
        $this->addSql('CREATE INDEX IDX_E3EA049916A2B381 ON app_activities (book_id)');
        $this->addSql('DROP INDEX IDX_2DFDA3CB16A2B381');
        $this->addSql('DROP INDEX IDX_2DFDA3CBF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authors_books AS SELECT author_id, book_id FROM authors_books');
        $this->addSql('DROP TABLE authors_books');
        $this->addSql('CREATE TABLE authors_books (author_id INTEGER NOT NULL, book_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(author_id, book_id), CONSTRAINT FK_2DFDA3CBF675F31B FOREIGN KEY (author_id) REFERENCES app_authors (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2DFDA3CB16A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authors_books (author_id, book_id) SELECT author_id, book_id FROM __temp__authors_books');
        $this->addSql('DROP TABLE __temp__authors_books');
        $this->addSql('CREATE INDEX IDX_2DFDA3CB16A2B381 ON authors_books (book_id)');
        $this->addSql('CREATE INDEX IDX_2DFDA3CBF675F31B ON authors_books (author_id)');
        $this->addSql('DROP INDEX IDX_9CC8A75F3E2B4156');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_books AS SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, ratings, times_borrowed FROM app_books');
        $this->addSql('DROP TABLE app_books');
        $this->addSql('CREATE TABLE app_books (id INTEGER NOT NULL, bookserie_id INTEGER DEFAULT NULL, isbn VARCHAR(255) NOT NULL COLLATE BINARY, title VARCHAR(255) NOT NULL COLLATE BINARY, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, publisher VARCHAR(255) NOT NULL COLLATE BINARY, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL COLLATE BINARY, annotation VARCHAR(255) NOT NULL COLLATE BINARY, ratings CLOB NOT NULL COLLATE BINARY --(DC2Type:json_array)
        , times_borrowed INTEGER DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_9CC8A75F3E2B4156 FOREIGN KEY (bookserie_id) REFERENCES app_book_series (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_books (id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, ratings, times_borrowed) SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, ratings, times_borrowed FROM __temp__app_books');
        $this->addSql('DROP TABLE __temp__app_books');
        $this->addSql('CREATE INDEX IDX_9CC8A75F3E2B4156 ON app_books (bookserie_id)');
        $this->addSql('DROP INDEX IDX_FD9C07D94908CA01');
        $this->addSql('DROP INDEX IDX_FD9C07D9A33F7DF7');
        $this->addSql('CREATE TEMPORARY TABLE __temp__books_and_genres AS SELECT bookId, genreId FROM books_and_genres');
        $this->addSql('DROP TABLE books_and_genres');
        $this->addSql('CREATE TABLE books_and_genres (bookId INTEGER UNSIGNED NOT NULL, genreId INTEGER UNSIGNED NOT NULL, PRIMARY KEY(bookId, genreId), CONSTRAINT FK_FD9C07D9A33F7DF7 FOREIGN KEY (bookId) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FD9C07D94908CA01 FOREIGN KEY (genreId) REFERENCES app_genres (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO books_and_genres (bookId, genreId) SELECT bookId, genreId FROM __temp__books_and_genres');
        $this->addSql('DROP TABLE __temp__books_and_genres');
        $this->addSql('CREATE INDEX IDX_FD9C07D94908CA01 ON books_and_genres (genreId)');
        $this->addSql('CREATE INDEX IDX_FD9C07D9A33F7DF7 ON books_and_genres (bookId)');
        $this->addSql('DROP INDEX IDX_F8E9C5FE1717D737');
        $this->addSql('DROP INDEX IDX_F8E9C5FE16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_book_reservations AS SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM app_book_reservations');
        $this->addSql('DROP TABLE app_book_reservations');
        $this->addSql('CREATE TABLE app_book_reservations (id INTEGER NOT NULL, book_id INTEGER UNSIGNED DEFAULT NULL, reader_id INTEGER DEFAULT NULL, date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL COLLATE BINARY, fine DOUBLE PRECISION NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_F8E9C5FE16A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F8E9C5FE1717D737 FOREIGN KEY (reader_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_book_reservations (id, book_id, reader_id, date_from, date_to, status, fine) SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM __temp__app_book_reservations');
        $this->addSql('DROP TABLE __temp__app_book_reservations');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE1717D737 ON app_book_reservations (reader_id)');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE16A2B381 ON app_book_reservations (book_id)');
        $this->addSql('DROP INDEX IDX_571EF687A76ED395');
        $this->addSql('DROP INDEX IDX_571EF68716A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_comments AS SELECT id, book_id, user_id, published_at, content FROM app_comments');
        $this->addSql('DROP TABLE app_comments');
        $this->addSql('CREATE TABLE app_comments (id INTEGER NOT NULL, book_id INTEGER UNSIGNED DEFAULT NULL, user_id INTEGER DEFAULT NULL, published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_571EF68716A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_571EF687A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_comments (id, book_id, user_id, published_at, content) SELECT id, book_id, user_id, published_at, content FROM __temp__app_comments');
        $this->addSql('DROP TABLE __temp__app_comments');
        $this->addSql('CREATE INDEX IDX_571EF687A76ED395 ON app_comments (user_id)');
        $this->addSql('CREATE INDEX IDX_571EF68716A2B381 ON app_comments (book_id)');
        $this->addSql('DROP INDEX IDX_FA7D8D7FCD53EDB6');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_notifications AS SELECT id, receiver_id, title, content, is_seen FROM app_notifications');
        $this->addSql('DROP TABLE app_notifications');
        $this->addSql('CREATE TABLE app_notifications (id INTEGER NOT NULL, receiver_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, content VARCHAR(255) NOT NULL COLLATE BINARY, is_seen VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_FA7D8D7FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_notifications (id, receiver_id, title, content, is_seen) SELECT id, receiver_id, title, content, is_seen FROM __temp__app_notifications');
        $this->addSql('DROP TABLE __temp__app_notifications');
        $this->addSql('CREATE INDEX IDX_FA7D8D7FCD53EDB6 ON app_notifications (receiver_id)');
        $this->addSql('DROP INDEX UNIQ_AD6C8EDB16A2B381');
        $this->addSql('DROP INDEX IDX_AD6C8EDBA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users_books AS SELECT user_id, book_id FROM users_books');
        $this->addSql('DROP TABLE users_books');
        $this->addSql('CREATE TABLE users_books (user_id INTEGER NOT NULL, book_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(user_id, book_id), CONSTRAINT FK_AD6C8EDBA76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD6C8EDB16A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO users_books (user_id, book_id) SELECT user_id, book_id FROM __temp__users_books');
        $this->addSql('DROP TABLE __temp__users_books');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD6C8EDB16A2B381 ON users_books (book_id)');
        $this->addSql('CREATE INDEX IDX_AD6C8EDBA76ED395 ON users_books (user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_E3EA049916A2B381');
        $this->addSql('DROP INDEX IDX_E3EA0499A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_activities AS SELECT id, book_id, user_id, title, content, time FROM app_activities');
        $this->addSql('DROP TABLE app_activities');
        $this->addSql('CREATE TABLE app_activities (id INTEGER NOT NULL, book_id INTEGER UNSIGNED DEFAULT NULL, user_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, time DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_activities (id, book_id, user_id, title, content, time) SELECT id, book_id, user_id, title, content, time FROM __temp__app_activities');
        $this->addSql('DROP TABLE __temp__app_activities');
        $this->addSql('CREATE INDEX IDX_E3EA049916A2B381 ON app_activities (book_id)');
        $this->addSql('CREATE INDEX IDX_E3EA0499A76ED395 ON app_activities (user_id)');
        $this->addSql('DROP INDEX IDX_F8E9C5FE16A2B381');
        $this->addSql('DROP INDEX IDX_F8E9C5FE1717D737');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_book_reservations AS SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM app_book_reservations');
        $this->addSql('DROP TABLE app_book_reservations');
        $this->addSql('CREATE TABLE app_book_reservations (id INTEGER NOT NULL, book_id INTEGER UNSIGNED DEFAULT NULL, reader_id INTEGER DEFAULT NULL, date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL, fine DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_book_reservations (id, book_id, reader_id, date_from, date_to, status, fine) SELECT id, book_id, reader_id, date_from, date_to, status, fine FROM __temp__app_book_reservations');
        $this->addSql('DROP TABLE __temp__app_book_reservations');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE16A2B381 ON app_book_reservations (book_id)');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE1717D737 ON app_book_reservations (reader_id)');
        $this->addSql('DROP INDEX IDX_9CC8A75F3E2B4156');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_books AS SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, ratings, times_borrowed FROM app_books');
        $this->addSql('DROP TABLE app_books');
        $this->addSql('CREATE TABLE app_books (id INTEGER NOT NULL, bookserie_id INTEGER DEFAULT NULL, isbn VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL, publisher VARCHAR(255) NOT NULL, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL, annotation VARCHAR(255) NOT NULL, ratings CLOB NOT NULL --(DC2Type:json_array)
        , times_borrowed INTEGER DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_books (id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, ratings, times_borrowed) SELECT id, bookserie_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, ratings, times_borrowed FROM __temp__app_books');
        $this->addSql('DROP TABLE __temp__app_books');
        $this->addSql('CREATE INDEX IDX_9CC8A75F3E2B4156 ON app_books (bookserie_id)');
        $this->addSql('DROP INDEX IDX_571EF68716A2B381');
        $this->addSql('DROP INDEX IDX_571EF687A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_comments AS SELECT id, book_id, user_id, published_at, content FROM app_comments');
        $this->addSql('DROP TABLE app_comments');
        $this->addSql('CREATE TABLE app_comments (id INTEGER NOT NULL, book_id INTEGER UNSIGNED DEFAULT NULL, user_id INTEGER DEFAULT NULL, published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_comments (id, book_id, user_id, published_at, content) SELECT id, book_id, user_id, published_at, content FROM __temp__app_comments');
        $this->addSql('DROP TABLE __temp__app_comments');
        $this->addSql('CREATE INDEX IDX_571EF68716A2B381 ON app_comments (book_id)');
        $this->addSql('CREATE INDEX IDX_571EF687A76ED395 ON app_comments (user_id)');
        $this->addSql('DROP INDEX IDX_FA7D8D7FCD53EDB6');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_notifications AS SELECT id, receiver_id, title, content, is_seen FROM app_notifications');
        $this->addSql('DROP TABLE app_notifications');
        $this->addSql('CREATE TABLE app_notifications (id INTEGER NOT NULL, receiver_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, is_seen VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_notifications (id, receiver_id, title, content, is_seen) SELECT id, receiver_id, title, content, is_seen FROM __temp__app_notifications');
        $this->addSql('DROP TABLE __temp__app_notifications');
        $this->addSql('CREATE INDEX IDX_FA7D8D7FCD53EDB6 ON app_notifications (receiver_id)');
        $this->addSql('DROP INDEX IDX_2DFDA3CBF675F31B');
        $this->addSql('DROP INDEX IDX_2DFDA3CB16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authors_books AS SELECT author_id, book_id FROM authors_books');
        $this->addSql('DROP TABLE authors_books');
        $this->addSql('CREATE TABLE authors_books (author_id INTEGER NOT NULL, book_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(author_id, book_id))');
        $this->addSql('INSERT INTO authors_books (author_id, book_id) SELECT author_id, book_id FROM __temp__authors_books');
        $this->addSql('DROP TABLE __temp__authors_books');
        $this->addSql('CREATE INDEX IDX_2DFDA3CBF675F31B ON authors_books (author_id)');
        $this->addSql('CREATE INDEX IDX_2DFDA3CB16A2B381 ON authors_books (book_id)');
        $this->addSql('DROP INDEX IDX_FD9C07D9A33F7DF7');
        $this->addSql('DROP INDEX IDX_FD9C07D94908CA01');
        $this->addSql('CREATE TEMPORARY TABLE __temp__books_and_genres AS SELECT bookId, genreId FROM books_and_genres');
        $this->addSql('DROP TABLE books_and_genres');
        $this->addSql('CREATE TABLE books_and_genres (bookId INTEGER UNSIGNED NOT NULL, genreId INTEGER UNSIGNED NOT NULL, PRIMARY KEY(bookId, genreId))');
        $this->addSql('INSERT INTO books_and_genres (bookId, genreId) SELECT bookId, genreId FROM __temp__books_and_genres');
        $this->addSql('DROP TABLE __temp__books_and_genres');
        $this->addSql('CREATE INDEX IDX_FD9C07D9A33F7DF7 ON books_and_genres (bookId)');
        $this->addSql('CREATE INDEX IDX_FD9C07D94908CA01 ON books_and_genres (genreId)');
        $this->addSql('DROP INDEX IDX_AD6C8EDBA76ED395');
        $this->addSql('DROP INDEX UNIQ_AD6C8EDB16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users_books AS SELECT user_id, book_id FROM users_books');
        $this->addSql('DROP TABLE users_books');
        $this->addSql('CREATE TABLE users_books (user_id INTEGER NOT NULL, book_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(user_id, book_id))');
        $this->addSql('INSERT INTO users_books (user_id, book_id) SELECT user_id, book_id FROM __temp__users_books');
        $this->addSql('DROP TABLE __temp__users_books');
        $this->addSql('CREATE INDEX IDX_AD6C8EDBA76ED395 ON users_books (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD6C8EDB16A2B381 ON users_books (book_id)');
    }
}
