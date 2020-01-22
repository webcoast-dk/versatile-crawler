CREATE TABLE tx_versatilecrawler_domain_model_configuration (
  uid int(11) NOT NULL auto_increment,
  pid int(11) NOT NULL DEFAULT '0',
  crdate int(11) NOT NULL DEFAULT '0',
  cruser_id int(11) NOT NULL DEFAULT '0',
  tstamp int(11) NOT NULL DEFAULT '0',
  deleted int(11) NOT NULL DEFAULT '0',
  disabled int(11) NOT NULL DEFAULT '0',

  title varchar(100) NOT NULL DEFAULT '',
  domain int(11) NOT NULL DEFAULT '0',
  base_url varchar(200) NOT NULL DEFAULT '',
  type varchar(200) NOT NULL DEFAULT '',

  # fields for page indexer
  levels tinyint(2) NOT NULL DEFAULT '0',
  exclude_pages_with_configuration tinyint(1) NOT NULL DEFAULT '1',

  # languages field, is used by page and record indexer
  languages varchar(1000) NOT NULL DEFAULT '',

  # fields for record indexer
  table_name varchar(500) NOT NULL DEFAULT '',
  record_storage_page int(11) NOT NULL DEFAULT '0',
  record_storage_page_recursive tinyint(1) NOT NULL DEFAULT '0',
  query_string varchar(1000) NOT NULL DEFAULT '',

  # fields for meta configuration
  configurations int(11) NOT NULL DEFAULT '0',

  # fields for files configuration
  file_storages int(11) NOT NULL DEFAULT '0',
  file_extensions varchar(200) NOT NULL DEFAULT '',

  PRIMARY KEY (uid),
  KEY parent(pid)
) Engine = InnoDB;

CREATE TABLE tx_versatilecrawler_domain_model_configuration_mm (
  uid_local int(11) NOT NULL DEFAULT '0',
  uid_foreign int(11) NOT NULL DEFAULT '0',
  sorting int(11) NOT NULL DEFAULT '0'
) Engine = InnoDB;

CREATE TABLE tx_versatilecrawler_domain_model_configuration_file_storage_mm (
  uid_local int(11) NOT NULL DEFAULT '0',
  uid_foreign int(11) NOT NULL DEFAULT '0',
  sorting int(11) NOT NULL DEFAULT '0'
) Engine = InnoDB;

CREATE TABLE tx_versatilecrawler_domain_model_queue_item (
  uid int(11) NOT NULL auto_increment,
  pid int(11) NOT NULL DEFAULT '0',
  tstamp int(11) NOT NULL DEFAULT '0',

  configuration int(11) NOT NULL DEFAULT '0',
  identifier varchar(300) NOT NULL DEFAULT '0',
  hash varchar(32) NOT NULL DEFAULT '',
  state tinyint(1) NOT NULL DEFAULT '0',
  message text,
  data json,

  PRIMARY KEY (uid),
  KEY parent(pid)
) Engine = InnoDB;
