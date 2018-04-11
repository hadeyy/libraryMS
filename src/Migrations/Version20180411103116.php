<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180411103116 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX UNIQ_AD6C8EDB16A2B381');
        $this->addSql('DROP INDEX IDX_AD6C8EDBA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users_books AS SELECT user_id, book_id FROM users_books');
        $this->addSql('DROP TABLE users_books');
        $this->addSql('CREATE TABLE users_books (user_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , book_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(user_id, book_id), CONSTRAINT FK_AD6C8EDBA76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD6C8EDB16A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO users_books (user_id, book_id) SELECT user_id, book_id FROM __temp__users_books');
        $this->addSql('DROP TABLE __temp__users_books');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD6C8EDB16A2B381 ON users_books (book_id)');
        $this->addSql('CREATE INDEX IDX_AD6C8EDBA76ED395 ON users_books (user_id)');
        $this->addSql('DROP INDEX IDX_571EF687A76ED395');
        $this->addSql('DROP INDEX IDX_571EF68716A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_comments AS SELECT id, book_id, user_id, published_at, content FROM app_comments');
        $this->addSql('DROP TABLE app_comments');
        $this->addSql('CREATE TABLE app_comments (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_571EF68716A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_571EF687A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_comments (id, book_id, user_id, published_at, content) SELECT id, book_id, user_id, published_at, content FROM __temp__app_comments');
        $this->addSql('DROP TABLE __temp__app_comments');
        $this->addSql('CREATE INDEX IDX_571EF687A76ED395 ON app_comments (user_id)');
        $this->addSql('CREATE INDEX IDX_571EF68716A2B381 ON app_comments (book_id)');
        $this->addSql('DROP INDEX IDX_9CC8A75FF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_books AS SELECT id, author_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, times_borrowed, slug FROM app_books');
        $this->addSql('DROP TABLE app_books');
        $this->addSql('CREATE TABLE app_books (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , author_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , isbn VARCHAR(255) NOT NULL COLLATE BINARY, title VARCHAR(255) NOT NULL COLLATE BINARY, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL COLLATE BINARY, publisher VARCHAR(255) NOT NULL COLLATE BINARY, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL COLLATE BINARY, annotation VARCHAR(255) NOT NULL COLLATE BINARY, times_borrowed INTEGER NOT NULL, slug VARCHAR(255) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_9CC8A75FF675F31B FOREIGN KEY (author_id) REFERENCES app_authors (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_books (id, author_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, times_borrowed, slug) SELECT id, author_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, times_borrowed, slug FROM __temp__app_books');
        $this->addSql('DROP TABLE __temp__app_books');
        $this->addSql('CREATE INDEX IDX_9CC8A75FF675F31B ON app_books (author_id)');
        $this->addSql('DROP INDEX IDX_FD9C07D94908CA01');
        $this->addSql('DROP INDEX IDX_FD9C07D9A33F7DF7');
        $this->addSql('CREATE TEMPORARY TABLE __temp__books_and_genres AS SELECT bookId, genreId FROM books_and_genres');
        $this->addSql('DROP TABLE books_and_genres');
        $this->addSql('CREATE TABLE books_and_genres (bookId CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , genreId CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(bookId, genreId), CONSTRAINT FK_FD9C07D9A33F7DF7 FOREIGN KEY (bookId) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FD9C07D94908CA01 FOREIGN KEY (genreId) REFERENCES app_genres (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO books_and_genres (bookId, genreId) SELECT bookId, genreId FROM __temp__books_and_genres');
        $this->addSql('DROP TABLE __temp__books_and_genres');
        $this->addSql('CREATE INDEX IDX_FD9C07D94908CA01 ON books_and_genres (genreId)');
        $this->addSql('CREATE INDEX IDX_FD9C07D9A33F7DF7 ON books_and_genres (bookId)');
        $this->addSql('DROP INDEX IDX_F8E9C5FE1717D737');
        $this->addSql('DROP INDEX IDX_F8E9C5FE16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_book_reservations AS SELECT id, book_id, reader_id, date_from, date_to, status, updated_at FROM app_book_reservations');
        $this->addSql('DROP TABLE app_book_reservations');
        $this->addSql('CREATE TABLE app_book_reservations (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , reader_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL COLLATE BINARY, updated_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_F8E9C5FE16A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F8E9C5FE1717D737 FOREIGN KEY (reader_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_book_reservations (id, book_id, reader_id, date_from, date_to, status, updated_at) SELECT id, book_id, reader_id, date_from, date_to, status, updated_at FROM __temp__app_book_reservations');
        $this->addSql('DROP TABLE __temp__app_book_reservations');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE1717D737 ON app_book_reservations (reader_id)');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE16A2B381 ON app_book_reservations (book_id)');
        $this->addSql('DROP INDEX IDX_E3EA0499A76ED395');
        $this->addSql('DROP INDEX IDX_E3EA049916A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_activities AS SELECT id, book_id, user_id, title, time FROM app_activities');
        $this->addSql('DROP TABLE app_activities');
        $this->addSql('CREATE TABLE app_activities (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , title VARCHAR(255) NOT NULL COLLATE BINARY, time DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_E3EA049916A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E3EA0499A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_activities (id, book_id, user_id, title, time) SELECT id, book_id, user_id, title, time FROM __temp__app_activities');
        $this->addSql('DROP TABLE __temp__app_activities');
        $this->addSql('CREATE INDEX IDX_E3EA0499A76ED395 ON app_activities (user_id)');
        $this->addSql('CREATE INDEX IDX_E3EA049916A2B381 ON app_activities (book_id)');
        $this->addSql('DROP INDEX IDX_23EB8784A76ED395');
        $this->addSql('DROP INDEX IDX_23EB878416A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_ratings AS SELECT id, book_id, user_id, value FROM app_ratings');
        $this->addSql('DROP TABLE app_ratings');
        $this->addSql('CREATE TABLE app_ratings (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , value INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_23EB878416A2B381 FOREIGN KEY (book_id) REFERENCES app_books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_23EB8784A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO app_ratings (id, book_id, user_id, value) SELECT id, book_id, user_id, value FROM __temp__app_ratings');
        $this->addSql('DROP TABLE __temp__app_ratings');
        $this->addSql('CREATE INDEX IDX_23EB8784A76ED395 ON app_ratings (user_id)');
        $this->addSql('CREATE INDEX IDX_23EB878416A2B381 ON app_ratings (book_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_E3EA049916A2B381');
        $this->addSql('DROP INDEX IDX_E3EA0499A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_activities AS SELECT id, book_id, user_id, title, time FROM app_activities');
        $this->addSql('DROP TABLE app_activities');
        $this->addSql('CREATE TABLE app_activities (id CHAR(36) NOT NULL --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , title VARCHAR(255) NOT NULL, time DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_activities (id, book_id, user_id, title, time) SELECT id, book_id, user_id, title, time FROM __temp__app_activities');
        $this->addSql('DROP TABLE __temp__app_activities');
        $this->addSql('CREATE INDEX IDX_E3EA049916A2B381 ON app_activities (book_id)');
        $this->addSql('CREATE INDEX IDX_E3EA0499A76ED395 ON app_activities (user_id)');
        $this->addSql('DROP INDEX IDX_F8E9C5FE16A2B381');
        $this->addSql('DROP INDEX IDX_F8E9C5FE1717D737');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_book_reservations AS SELECT id, book_id, reader_id, date_from, date_to, status, updated_at FROM app_book_reservations');
        $this->addSql('DROP TABLE app_book_reservations');
        $this->addSql('CREATE TABLE app_book_reservations (id CHAR(36) NOT NULL --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , reader_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, status VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_book_reservations (id, book_id, reader_id, date_from, date_to, status, updated_at) SELECT id, book_id, reader_id, date_from, date_to, status, updated_at FROM __temp__app_book_reservations');
        $this->addSql('DROP TABLE __temp__app_book_reservations');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE16A2B381 ON app_book_reservations (book_id)');
        $this->addSql('CREATE INDEX IDX_F8E9C5FE1717D737 ON app_book_reservations (reader_id)');
        $this->addSql('DROP INDEX IDX_9CC8A75FF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_books AS SELECT id, author_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, times_borrowed, slug FROM app_books');
        $this->addSql('DROP TABLE app_books');
        $this->addSql('CREATE TABLE app_books (id CHAR(36) NOT NULL --(DC2Type:guid)
        , author_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , isbn VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, pages INTEGER NOT NULL, language VARCHAR(255) NOT NULL, publisher VARCHAR(255) NOT NULL, publication_date DATETIME NOT NULL, available_copies INTEGER NOT NULL, reserved_copies INTEGER NOT NULL, cover VARCHAR(255) NOT NULL, annotation VARCHAR(255) NOT NULL, times_borrowed INTEGER NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_books (id, author_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, times_borrowed, slug) SELECT id, author_id, isbn, title, pages, language, publisher, publication_date, available_copies, reserved_copies, cover, annotation, times_borrowed, slug FROM __temp__app_books');
        $this->addSql('DROP TABLE __temp__app_books');
        $this->addSql('CREATE INDEX IDX_9CC8A75FF675F31B ON app_books (author_id)');
        $this->addSql('DROP INDEX IDX_571EF68716A2B381');
        $this->addSql('DROP INDEX IDX_571EF687A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_comments AS SELECT id, book_id, user_id, published_at, content FROM app_comments');
        $this->addSql('DROP TABLE app_comments');
        $this->addSql('CREATE TABLE app_comments (id CHAR(36) NOT NULL --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , published_at DATETIME NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_comments (id, book_id, user_id, published_at, content) SELECT id, book_id, user_id, published_at, content FROM __temp__app_comments');
        $this->addSql('DROP TABLE __temp__app_comments');
        $this->addSql('CREATE INDEX IDX_571EF68716A2B381 ON app_comments (book_id)');
        $this->addSql('CREATE INDEX IDX_571EF687A76ED395 ON app_comments (user_id)');
        $this->addSql('DROP INDEX IDX_23EB878416A2B381');
        $this->addSql('DROP INDEX IDX_23EB8784A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__app_ratings AS SELECT id, book_id, user_id, value FROM app_ratings');
        $this->addSql('DROP TABLE app_ratings');
        $this->addSql('CREATE TABLE app_ratings (id CHAR(36) NOT NULL --(DC2Type:guid)
        , book_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , value INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO app_ratings (id, book_id, user_id, value) SELECT id, book_id, user_id, value FROM __temp__app_ratings');
        $this->addSql('DROP TABLE __temp__app_ratings');
        $this->addSql('CREATE INDEX IDX_23EB878416A2B381 ON app_ratings (book_id)');
        $this->addSql('CREATE INDEX IDX_23EB8784A76ED395 ON app_ratings (user_id)');
        $this->addSql('DROP INDEX IDX_FD9C07D9A33F7DF7');
        $this->addSql('DROP INDEX IDX_FD9C07D94908CA01');
        $this->addSql('CREATE TEMPORARY TABLE __temp__books_and_genres AS SELECT bookId, genreId FROM books_and_genres');
        $this->addSql('DROP TABLE books_and_genres');
        $this->addSql('CREATE TABLE books_and_genres (bookId CHAR(36) NOT NULL --(DC2Type:guid)
        , genreId CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(bookId, genreId))');
        $this->addSql('INSERT INTO books_and_genres (bookId, genreId) SELECT bookId, genreId FROM __temp__books_and_genres');
        $this->addSql('DROP TABLE __temp__books_and_genres');
        $this->addSql('CREATE INDEX IDX_FD9C07D9A33F7DF7 ON books_and_genres (bookId)');
        $this->addSql('CREATE INDEX IDX_FD9C07D94908CA01 ON books_and_genres (genreId)');
        $this->addSql('DROP INDEX IDX_AD6C8EDBA76ED395');
        $this->addSql('DROP INDEX UNIQ_AD6C8EDB16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users_books AS SELECT user_id, book_id FROM users_books');
        $this->addSql('DROP TABLE users_books');
        $this->addSql('CREATE TABLE users_books (user_id CHAR(36) NOT NULL --(DC2Type:guid)
        , book_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(user_id, book_id))');
        $this->addSql('INSERT INTO users_books (user_id, book_id) SELECT user_id, book_id FROM __temp__users_books');
        $this->addSql('DROP TABLE __temp__users_books');
        $this->addSql('CREATE INDEX IDX_AD6C8EDBA76ED395 ON users_books (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD6C8EDB16A2B381 ON users_books (book_id)');
    }
}
