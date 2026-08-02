-- =============================================
--  EMERALD LIBRARY - Database Schema & Seed Data
--  Import via: mysql -u root < library.sql
--  Or run install.php in the browser.
-- =============================================

CREATE DATABASE IF NOT EXISTS library_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE library_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS issues;
DROP TABLE IF EXISTS borrow_requests;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------
-- USERS
-- ---------------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- CATEGORIES
-- ---------------------------------------------------------------
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- BOOKS
-- ---------------------------------------------------------------
CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(150) NOT NULL,
  isbn VARCHAR(50) DEFAULT NULL,
  category_id INT DEFAULT NULL,
  total_copies INT NOT NULL DEFAULT 1,
  available_copies INT NOT NULL DEFAULT 1,
  cover VARCHAR(255) DEFAULT NULL,
  description TEXT,
  content LONGTEXT,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_books_category (category_id),
  FULLTEXT INDEX ft_books (title, author),
  CONSTRAINT fk_books_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- BORROW REQUESTS
-- ---------------------------------------------------------------
CREATE TABLE borrow_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  status ENUM('pending','approved','rejected','cancelled','returned') NOT NULL DEFAULT 'pending',
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_req_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_req_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- ISSUES (borrowed / returned records)
-- ---------------------------------------------------------------
CREATE TABLE issues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  request_id INT DEFAULT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE DEFAULT NULL,
  fine DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  status ENUM('issued','returned') NOT NULL DEFAULT 'issued',
  CONSTRAINT fk_issue_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_issue_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===============================================================
-- SEED DATA
-- ===============================================================

-- Passwords:  admin123 / user123  (bcrypt hashes)
INSERT INTO users (name, email, password, role) VALUES
('Library Administrator', 'admin@library.com', '$2y$12$igSnGvNiXaIq4pekcXRB4.ScKji8rcHKczsLWwiM2yoylqiTME0wC', 'admin'),
('Alex Reader', 'user@library.com', '$2y$12$DuO9dwduvs4vKs.mg/B5Yu62i35UFSYTjafEtctaKODYp/wNbuNr.', 'user'),
('Jane Smith', 'jane@library.com', '$2y$12$DuO9dwduvs4vKs.mg/B5Yu62i35UFSYTjafEtctaKODYp/wNbuNr.', 'user');

INSERT INTO categories (name, description) VALUES
('Fiction', 'Novels, classics and short stories.'),
('Science', 'Scientific discoveries and explorations.'),
('History', 'History, philosophy and ancient wisdom.'),
('Mystery', 'Thrillers, adventures and mysteries.'),
('Technology', 'Software, engineering and the modern world.'),
('Biography', 'Life stories of remarkable people.');

INSERT INTO books (title, author, isbn, category_id, total_copies, available_copies, description, content) VALUES
('Pride and Prejudice', 'Jane Austen', '978-0141439518', 1, 5, 5,
 'A sparkling comedy of manners about love, class and family in Georgian England.',
 'It is a truth universally acknowledged, that a single man in possession of a good fortune, must be in want of a wife.

However little known the feelings or views of such a man may be on his first entering a neighbourhood, this truth is so well fixed in the minds of the surrounding families, that he is considered as the rightful property of some one or other of their daughters.

"My dear Mr. Bennet," said his lady to him one day, "have you heard that Netherfield Park is let at last?"

Mr. Bennet replied that he had not.

"But it is," returned she; "for Mrs. Long has just been here, and she told me all about it."

Mr. Bennet made no answer.

"Do not you want to know who has taken it?" cried his wife impatiently.

"You want to tell me, and I have no objection to hearing it."

This was invitation enough.

"Why, my dear, you must know, Mrs. Long says that Netherfield is taken by a young man of large fortune from the north of England; that he came down on Monday in a chaise and four to see the place, and was so much delighted with it that he agreed with Mr. Morris immediately."'),

('A Tale of Two Cities', 'Charles Dickens', '978-1853260005', 1, 4, 4,
 'A sweeping story of revolution, sacrifice and resurrection set between London and Paris.',
 'It was the best of times, it was the worst of times, it was the age of wisdom, it was the age of foolishness, it was the epoch of belief, it was the epoch of incredulity, it was the season of Light, it was the season of Darkness, it was the spring of hope, it was the winter of despair, we had everything before us, we had nothing before us, we were all going direct to Heaven, we were all going direct the other way -- in short, the period was so far like the present period, that some of its noisiest authorities insisted on its being received, for good or for evil, in the superlative degree of comparison only.

There were a king with a large jaw and a queen with a plain face, on the throne of England; there were a king with a large jaw and a queen with a fair face, on the throne of France. In both countries it was clearer than crystal to the lords of the State preserves of loaves and fishes, that things in general were settled for ever.

It was the year of Our Lord one thousand seven hundred and seventy-five. Spiritual revelations were conceded to England at that favoured period, as at this. Mrs. Southcott had recently attained her five-and-twentieth blessed birthday, of whom a prophetic private in the Life Guards had heralded the sublime appearance by announcing that arrangements were made for the swallowing up of London and Westminster.'),

('The Time Machine', 'H.G. Wells', '978-0451528551', 2, 3, 3,
 'A Victorian inventor voyages into the far future and finds a world transformed.',
 'The Time Traveller (for so it will be convenient to speak of him) was expounding a recondite matter to us. His pale grey eyes shone and twinkled, and his usually pale face was flushed and animated. The fire burnt brightly, and the soft radiance of the incandescent lights in the lilies of silver caught the bubbles that flashed and passed in our glasses.

"What is this?" said I. "Is this possible?"

"It is a work of fiction," replied the Time Traveller.

"But surely you can prove it," I said.

"I will prove it," said the Time Traveller, "if you will spare me a few minutes."

He took up a little model of a machine, set it upon the table, and drew a chair towards it. His listeners sat forward, and for a while the only sound in the room was the quiet ticking of the strange device.'),

('Meditations', 'Marcus Aurelius', '978-0140449334', 3, 6, 6,
 'Personal reflections of a Roman emperor on virtue, duty and the art of living.',
 'Begin the morning by saying to thyself, I shall meet with the busy-body, the ungrateful, arrogant, deceitful, envious, unsocial. All these things happen to them by reason of their ignorance of what is good and evil.

But I who have seen the nature of the good that it is beautiful, and of the bad that it is ugly, and the nature of him who does wrong, that it is akin to me, not only of the same blood or seed, but that it participates in the same intelligence and the same portion of the divinity, I can neither be injured by any of them, for no one can fix on me what is ugly, nor can I be angry with my kinsman, nor hate him.

We are made for co-operation, like feet, like hands, like eyelids, like the rows of the upper and lower teeth. To act against one another then is contrary to nature; and it is acting against one another to be vexed and to turn away.'),

('A Brief History of Time', 'Stephen Hawking', '978-0553380163', 2, 4, 4,
 'From the Big Bang to black holes, a journey through the universe.',
 'A well-known scientist (some say it was Bertrand Russell) once gave a public lecture on astronomy. He described how the earth orbits around the sun and how the sun, in turn, orbits around the center of a vast collection of stars called our galaxy.

At the end of the lecture, a little old lady at the back of the room got up and said: "What you have told us is rubbish. The world is really a flat plate supported on the back of a giant tortoise."

The scientist gave a superior smile before replying, "What is the tortoise standing on?"

"You''re very clever, young man, very clever," said the old lady. "But it''s turtles all the way down!"

Most people would find the picture of our universe as an infinite tower of tortoises rather ridiculous, but why do we think we know better? What do we know about the universe, and how do we know it? Where did the universe come from, and where is it going?'),

('The Art of War', 'Sun Tzu', '978-1599869773', 3, 5, 5,
 'The classic treatise on strategy, discipline and victory without battle.',
 'Sun Tzu said: The art of war is of vital importance to the State. It is a matter of life and death, a road either to safety or to ruin. Hence it is a subject of inquiry which can on no account be neglected.

The art of war, then, is governed by five constant factors, to be taken into account in one''s deliberations, when seeking to determine the conditions obtaining in the field. These are: The Moral Law; Heaven; Earth; The Commander; Method and discipline.

The Moral Law causes the people to be in complete accord with their ruler, so that they will follow him regardless of their lives, undismayed by any danger. Heaven signifies night and day, cold and heat, times and seasons. Earth comprises distances, great and small; danger and security; open ground and narrow passes; the chances of life and death. The Commander stands for the virtues of wisdom, sincerity, benevolence, courage and strictness.'),

('Alice''s Adventures in Wonderland', 'Lewis Carroll', '978-1503222687', 4, 4, 4,
 'A curious girl falls down a rabbit hole into a world of the absurd and wonderful.',
 'Alice was beginning to get very tired of sitting by her sister on the bank, and of having nothing to do: once or twice she had peeped into the book her sister was reading, but it had no pictures or conversations in it, "and what is the use of a book," thought Alice, "without pictures or conversations?"

So she was considering in her own mind (as well as she could, for the hot day made her feel very sleepy and stupid), whether the pleasure of making a daisy-chain would be worth the trouble of getting up and picking the daisies, when suddenly a White Rabbit with pink eyes ran close by her.

There was nothing so very remarkable in that; nor did Alice think it so very much out of the way to hear the Rabbit say to itself, "Oh dear! Oh dear! I shall be late!" But when the Rabbit actually took a watch out of its waistcoat-pocket, and looked at it, and then hurried on, Alice started to her feet, for it flashed across her mind that she had never before seen a rabbit with either a waistcoat-pocket, or a watch to take out of it, and burning with curiosity, she ran across the field after it, and fortunately was just in time to see it pop down a large rabbit-hole under the hedge.'),

('Frankenstein', 'Mary Shelley', '978-0486282114', 4, 3, 3,
 'A scientist creates life in the laboratory and must face the consequences.',
 'You will rejoice to hear that no disaster has accompanied the commencement of an enterprise which you have regarded with such evil forebodings. I arrived here yesterday, and my first task is to assure my dear sister of my welfare and increasing confidence in the success of my undertaking.

I am already far north of London, and as I walk in the streets of Petersburgh, I feel a cold northern breeze play upon my cheeks, which braces my nerves and fills me with delight.

Do you understand this feeling? This breeze, which has travelled from the regions towards which I am advancing, gives me a foretaste of those icy climes. Inspirited by this wind of promise, my daydreams become more fervent and vivid. I try in vain to be persuaded that the pole is the seat of frost and desolation; it ever presents itself to my imagination as the region of beauty and delight.'),

('Clean Code', 'Robert C. Martin', '978-0132350884', 5, 7, 7,
 'A handbook of agile software craftsmanship for writing readable, maintainable code.',
 'Even bad code can function. But if code isn''t clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code.

Have you ever been frustrated by someone else''s code? Have you ever spent an entire week searching for a bug? Perhaps the code you were reading was badly named, or the functions were too long, or the data structures were tangled. Whatever the cause, the code probably was not clean.

As a programmer, your job is not only to make the computer understand the code, but also to make it clear to other human beings. Indeed, readable code is a form of courtesy to the next developer who will maintain the system. Readability is measured not by how much you understand, but by how quickly a stranger can understand it.');
