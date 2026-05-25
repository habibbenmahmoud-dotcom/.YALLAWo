CREATE DATABASE IF NOT EXISTS yallawork CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yallawork;
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  prenom        VARCHAR(100)  NOT NULL,
  nom           VARCHAR(100)  NOT NULL,
  email         VARCHAR(190)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('etudiant','entreprise','admin') NOT NULL DEFAULT 'etudiant',
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS offers (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  posted_by     INT           NULL,                         
  titre         VARCHAR(190)  NOT NULL,
  entreprise    VARCHAR(190)  NOT NULL,
  type_contrat  VARCHAR(50)   NOT NULL,
  ville         VARCHAR(100)  NOT NULL,
  salaire       VARCHAR(100),
  logo          VARCHAR(255)  DEFAULT '💼',                 
  description   TEXT,
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_offer_user FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS applications (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  offer_id      INT           NULL,
  user_id       INT           NULL,                         
  prenom        VARCHAR(100)  NOT NULL,
  nom           VARCHAR(100)  NOT NULL,
  email         VARCHAR(190)  NOT NULL,
  lettre        TEXT,
  cv_path       VARCHAR(255),
  status        ENUM('envoyee','vue','entretien','acceptee','refusee') DEFAULT 'envoyee',
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_offer FOREIGN KEY (offer_id)  REFERENCES offers(id) ON DELETE SET NULL,
  CONSTRAINT fk_app_user  FOREIGN KEY (user_id)   REFERENCES users(id)  ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS messages (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  sender_id     INT           NULL,
  receiver_id   INT           NULL,
  body          TEXT          NOT NULL,
  is_read       TINYINT(1)    NOT NULL DEFAULT 0,           -- FIX: 0=unread, 1=read
  read_at       TIMESTAMP     NULL     DEFAULT NULL,        -- FIX: when it was read
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_msg_sender   FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL
);
INSERT INTO offers (titre, entreprise, type_contrat, ville, salaire, logo, description) VALUES
('Développeur Full-Stack Junior',              'FinTech Maghreb S.A.',  'CDI',       'Tunis',  '2 800 – 3 500 TND', '🏦', 'React, PHP, MySQL'),
('Stage Data Science – Analyse Comportementale','E-Commerce Solutions', 'Stage',     'Sfax',   '600 TND/mois',      '🛒', 'Python, SQL, ML'),
('UX/UI Designer – Applications Mobile',       'Agence Pixel & Co',    'CDD',       'Sousse', '1 800 TND',         '🎨', 'Figma, prototypage'),
('Alternance – Cybersécurité & Audit SI',      'NordTech Consulting',   'Alternance','Ariana', '900 TND/mois',      '🔬', 'Sécurité, audit SI');
