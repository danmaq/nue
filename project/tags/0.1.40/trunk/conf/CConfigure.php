<?php

//////////////////////////////////////////////////////////////////////
// 設定ファイル。
// 設定可能な項目はもう少し下にあります。
//////////////////////////////////////////////////////////////////////

require_once(NUE_LIB_ROOT . '/CConstants.php');

/**
 *	設定クラス。
 */
class CConfigure
{

	//////////////////////////////////////////////////////////////////
	// >>>> ここから設定可能エリア
	//////////////////////////////////////////////////////////////////

	/**
	 *	サイト名を設定します。
	 */
	const SITE_NAME = 'danmaq';

	/**
	 *	カテゴリを指定しなかった場合に表示される、既定のカテゴリです。
	 *	現在のバージョンでは設定しても何も起きません。
	 */
	const DEFAULT_TAG = 'Diary';

	/**
	 *	1記事に登録可能な最大タグ数です。
	 */
	const TAG_MAX = 10;

	/**
	 *	使用するスキンを設定します。
	 */
	const SKINSET = 'default';

	/**
	 *	使用するDBMSを選択します。
	 *
	 *	現在のバージョンではMySQLのみ選択可能です。
	 *	将来的にはSQLiteも対応予定です。
	 */
	const DB_TYPE = CConstants::DBMS_MYSQL;

	/**
	 *	データベースのあるホスト名を設定します。
	 */
	const DB_HOST = 'localhost';

	/**
	 *	データベースへ接続するポート名を設定します。
	 */
	const DB_PORT = 3306;

	/**
	 *	データベースにログインするユーザIDを設定します。
	 */
	const DB_USER = 'SAMPLE_USER';

	/**
	 *	データベースにログインするパスワードを設定します。
	 */
	const DB_PASSWORD = 'SAMPLE_PASSWORD';

	/**
	 *	使用するデータベース名を設定します。
	 */
	const DB_NAME = 'SAMPLE_DATABASE';

	//////////////////////////////////////////////////////////////////
	// <<<< ここまで設定可能エリア
	//////////////////////////////////////////////////////////////////

}

?>
