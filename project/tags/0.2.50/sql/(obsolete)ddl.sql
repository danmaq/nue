CREATE TABLE IF NOT EXISTS NUE_M_AUTHORITY
(
	ID			TINYINT UNSIGNED	PRIMARY KEY	COMMENT '権限管理番号',
	DESCRIPTION	VARCHAR(255)		NOT NULL	COMMENT '解説'
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='権限マスタ';

CREATE TABLE IF NOT EXISTS NUE_T_ACCOUNT
(
	ID			TINYINT UNSIGNED					PRIMARY KEY	AUTO_INCREMENT				COMMENT 'ユーザ管理番号',
	NICKNAME	VARCHAR(255)						NOT NULL								COMMENT '名前',
	PASSWD		VARCHAR(255) CHARACTER SET ascii	NOT NULL								COMMENT 'パスワード',
	MASTER		BOOLEAN								NOT NULL								COMMENT '管理者長フラグ',
	CREATED		TIMESTAMP							NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT '作成日時',
	MODDED		TIMESTAMP							NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT '更新日時'
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ユーザ アカウント';

CREATE TABLE IF NOT EXISTS NUE_M_TAG
(
	ID			SMALLINT UNSIGNED	PRIMARY KEY	AUTO_INCREMENT	COMMENT 'タグ管理番号',
	TITLE		VARCHAR(255)		NOT NULL					COMMENT '名称',
	PARENT_ID	SMALLINT UNSIGNED								COMMENT '親タグ管理番号',
	FOREIGN KEY (ID) REFERENCES NUE_M_TAG(ID) ON DELETE SET NULL
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='タグマスタ';




CREATE TABLE IF NOT EXISTS NUE_T_SESSION
(
	id			CHAR(32)	CHARACTER SET ascii PRIMARY KEY	COMMENT 'ID',
	a_session	TEXT		NOT NULL						COMMENT '内容'
)
ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='セッション保持用';

