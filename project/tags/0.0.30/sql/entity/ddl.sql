CREATE TABLE IF NOT EXISTS NUE_ENTITIES
(
	ADDED_ID	INTEGER	UNSIGNED				NOT NULL	AUTO_INCREMENT PRIMARY KEY,
	ID			CHAR(36) CHARACTER SET ascii	NOT NULL	COMMENT 'ID',
	UPDATED		TIMESTAMP						NOT NULL	COMMENT '更新日時',
	BODY		TEXT										COMMENT '実情報',
	UNIQUE KEY (ID),
	KEY (UPDATED)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='実体';
