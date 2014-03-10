CREATE TABLE IF NOT EXISTS llx_immo_dict_type_compteur (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  code varchar(30) NOT NULL,
  intitule varchar(80) NOT NULL,
  sort smallint NOT NULL,
  active integer NULL,
  tms timestamp NOT NULL
) ENGINE=InnoDB;
