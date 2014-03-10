create table llx_paiementloyer
(
  rowid             integer AUTO_INCREMENT PRIMARY KEY,
  tms               timestamp,
  datec             datetime,          -- date de creation de l'enregistrement
  datep             datetime,          -- date de paiement
  amount            real DEFAULT 0,    -- montant
  fk_user_author    integer,           -- auteur
  fk_paiement       integer NOT NULL,  -- moyen de paiement
  num_paiement      varchar(50),       -- numero de paiement (cheque)
  note              text,
  fk_bank           integer NOT NULL,
  statut			smallint NOT NULL DEFAULT 0
)ENGINE=innodb;
