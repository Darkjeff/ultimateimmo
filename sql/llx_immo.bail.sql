CREATE TABLE llx_immo_bails (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  fk_prop integer NOT NULL,
  fk_loc integer NOT NULL,
  fk_logement INTEGER  NOT NULL  ,
  fk_mandat INTEGER  NULL  ,
  Type VARCHAR(25)  NULL  ,
  Date_location date  NULL  ,
  Depot_garantie VARCHAR(25)  NULL  ,
  date_fin date  NULL   ,
tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
entity integer NOT NULL DEFAULT 1
) ENGINE=InnoDB;
