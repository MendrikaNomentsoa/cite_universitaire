-- ============================================================
-- SCHEMA BASE DE DONNÉES - GESTION CITÉ UNIVERSITAIRE
-- PostgreSQL
-- ============================================================

CREATE TABLE ecole (
    numEcole        SERIAL PRIMARY KEY,
    nomEcole        VARCHAR(100) NOT NULL
);

CREATE TABLE utilisateur (
    numUtilisateur  SERIAL PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    tell            VARCHAR(20),
    email           VARCHAR(150) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    role            VARCHAR(50) NOT NULL DEFAULT 'agent'
);

CREATE TABLE etudiant (
    numEtudiant     SERIAL PRIMARY KEY,
    nomEtudiant     VARCHAR(100) NOT NULL,
    prenoms         VARCHAR(150) NOT NULL,
    anneeNaissance  DATE NOT NULL,
    numCin          VARCHAR(12),
    villeOriginaire VARCHAR(100),
    estExclus       BOOLEAN NOT NULL DEFAULT FALSE,
    niveau          VARCHAR(5) NOT NULL,
    situation       VARCHAR(20),
    sexe            CHAR(1) NOT NULL,
    email           VARCHAR(150) UNIQUE,
    numEcole        INT NOT NULL REFERENCES ecole(numEcole)
);

CREATE TABLE logement (
    numLogement     SERIAL PRIMARY KEY,
    placeDisponible INT NOT NULL DEFAULT 0,
    placeTotal      INT NOT NULL DEFAULT 0,
    etatLogement    VARCHAR(20) NOT NULL DEFAULT 'actif'
);

CREATE TABLE chambre (
    numChambre              SERIAL PRIMARY KEY,
    placeDisponibleChambre  INT NOT NULL DEFAULT 0,
    placeTotalChambre       INT NOT NULL DEFAULT 0,
    etatChambre             VARCHAR(20) NOT NULL DEFAULT 'actif',
    numLogement             INT NOT NULL REFERENCES logement(numLogement)
);

CREATE TABLE demande (
    numDemande      SERIAL PRIMARY KEY,
    dateDemande     DATE NOT NULL DEFAULT CURRENT_DATE,
    etatDemande     VARCHAR(20) NOT NULL DEFAULT 'attente'
);

CREATE TABLE habiter (
    numEtudiant         INT NOT NULL REFERENCES etudiant(numEtudiant),
    numLogement         INT NOT NULL REFERENCES logement(numLogement),
    numChambre          INT NOT NULL REFERENCES chambre(numChambre),
    debutInscription    DATE NOT NULL DEFAULT CURRENT_DATE,
    debutRenouvellement DATE,
    PRIMARY KEY (numEtudiant)
);

CREATE TABLE etudier (
    numEtudiant INT NOT NULL REFERENCES etudiant(numEtudiant),
    numEcole    INT NOT NULL REFERENCES ecole(numEcole),
    PRIMARY KEY (numEtudiant, numEcole)
);

CREATE TABLE gerer (
    numUtilisateur  INT NOT NULL REFERENCES utilisateur(numUtilisateur),
    numDemande      INT NOT NULL REFERENCES demande(numDemande),
    dateGestion     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (numUtilisateur, numDemande)
);

CREATE TABLE realiser (
    numEtudiant INT NOT NULL REFERENCES etudiant(numEtudiant),
    numDemande  INT NOT NULL REFERENCES demande(numDemande),
    PRIMARY KEY (numEtudiant, numDemande)
);

CREATE INDEX idx_etudiant_estExclus ON etudiant(estExclus);
CREATE INDEX idx_demande_etat ON demande(etatDemande);
CREATE INDEX idx_demande_date ON demande(dateDemande);
CREATE INDEX idx_chambre_logement ON chambre(numLogement);
CREATE INDEX idx_habiter_logement ON habiter(numLogement);
CREATE INDEX idx_habiter_chambre ON habiter(numChambre);
