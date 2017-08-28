<?php

/*
 * In the case of SQLite column is alias for rowid when it is integer
 * primary key, exactly integer, not smallint, not int, not unsigned.
 */
$sql =
"create table if not exists wikitran_lang
 (lang_id integer$specific,
  lang_code char(16) not null unique,
  timestamp datetime default current_timestamp not null,
  constraint pk_lang primary key (lang_id)
 );

create table if not exists wikitran_lang_name
 (name_id integer$specific,
  lang_code varchar(16) not null,
  name varchar(100) not null unique,
  name_lang varchar(16) not null,
  timestamp datetime default current_timestamp not null,
  constraint fk_lang_name_code foreign key (lang_code)
    references wikitran_lang (lang_code),
  constraint fk_name_lang foreign key (name_lang)
    references wikitran_lang (lang_code),
  constraint pk_lang_name primary key (name_id)
 );

create table if not exists wikitran_term
 (term_id integer$specific,
  timestamp datetime default current_timestamp not null,
  constraint pk_term primary key (term_id)
 );

create table if not exists wikitran_term_source
 (source_id integer$specific,
  source varchar(100) not null unique,
  timestamp datetime default current_timestamp not null,
  constraint pk_term_source primary key (source_id)
 );

create table if not exists wikitran_translation
 (term_id integer,
  trans_id integer$specific,
  trans varchar(255) not null,
  trans_lang varchar(16) not null,
  source_id integer,
  timestamp datetime default current_timestamp not null,
  constraint fk_term_id foreign key (term_id)
    references wikitran_term (term_id),
  constraint fk_trans_lang foreign key (trans_lang)
    references wikitran_lang (lang_code),
  constraint fk_source_id foreign key (source_id)
    references wikitran_term_source (source_id),
  constraint uc_translation unique (term_id, trans, trans_lang),
  constraint pk_translation primary key (trans_id)
 );

create table if not exists wikitran_term_relation
 (rel_id integer$specific,
  term_from integer,
  term_to integer,
  lang_from char(16) not null,
  lang_to char(16) not null,
  rel_src integer,
  timestamp datetime default current_timestamp not null,
  constraint fk_term_from foreign key (term_from)
    references wikitran_term (term_id),
  constraint fk_term_to foreign key (term_to)
    references wikitran_term (term_id),
  constraint fk_lang_from foreign key (lang_from)
    references wikitran_lang (lang_code),
  constraint fk_lang_to foreign key (lang_to)
    references wikitran_lang (lang_code),
  constraint fk_rel_src foreign key (rel_src)
    references wikitran_term_source (source_id),
  constraint uc_term_relation
    unique (term_from, term_to, lang_from, lang_to, rel_src),
  constraint pk_term_relation primary key (rel_id)
 );";
