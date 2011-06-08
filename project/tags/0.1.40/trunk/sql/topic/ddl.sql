CREATE TABLE IF NOT EXISTS NUE_INDEX_TOPIC
(
	ID	CHAR(36) CHARACTER SET ascii	NOT NULL	COMMENT 'ID',
	PRIMARY KEY (ID),
	FOREIGN KEY (ID) REFERENCES NUE_ENTITIES(ID) ON UPDATE CASCADE ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='トピック索引';