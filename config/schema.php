<?php

$sql =
"create table if not exists lang
 (lang_id integer$specific,
  lang_code char(16) not null unique,
  constraint pk_lang primary key (lang_id)
 );

create table if not exists lang_name
 (name_id integer$specific,
  lang_code varchar(16) not null,
  name varchar(100) not null unique,
  name_lang varchar(16) not null,
  constraint fk_lang_name_code foreign key (lang_code)
    references lang (lang_code),
  constraint fk_name_lang foreign key (name_lang)
    references lang (lang_code),
  constraint pk_lang_name primary key (name_id)
 );

create table if not exists term
 (term_id integer$specific,
  constraint pk_term primary key (term_id)
 );

create table if not exists term_source
 (source_id integer$specific,
  source varchar(100) not null unique,
  constraint pk_term_source primary key (source_id)
 );

create table if not exists translation
 (term_id integer,
  trans_id integer$specific,
  trans varchar(255) not null,
  trans_lang varchar(16) not null,
  source_id integer,
  constraint fk_term_id foreign key (term_id)
    references term (term_id),
  constraint fk_trans_lang foreign key (trans_lang)
    references lang (lang_code),
  constraint fk_source_id foreign key (source_id)
    references term_source (source_id),
  constraint uc_translation unique (term_id, trans, trans_lang),
  constraint pk_translation primary key (trans_id)
 );

create table if not exists term_relation
 (rel_id integer$specific,
  term_from integer,
  term_to integer,
  lang_from char(16) not null,
  lang_to char(16) not null,
  rel_src integer,
  constraint fk_term_from foreign key (term_from)
    references term (term_id),
  constraint fk_term_to foreign key (term_to)
    references term (term_id),
  constraint fk_lang_from foreign key (lang_from)
    references lang (lang_code),
  constraint fk_lang_to foreign key (lang_to)
    references lang (lang_code),
  constraint fk_rel_src foreign key (rel_src)
    references term_source (source_id),
  constraint uc_term_relation
    unique (term_from, term_to, lang_from, lang_to, rel_src),
  constraint pk_term_relation primary key (rel_id)
 );";