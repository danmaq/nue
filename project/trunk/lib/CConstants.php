<?php

//////////////////////////////////////////////////////////////////////
// このファイルは原則改変しないでください。
// プログラムが壊れる可能性があります。
//////////////////////////////////////////////////////////////////////

require_once(NUE_ROOT . '/conf/CConfigure.php');

/**
 *	定数クラス。
 */
class CConstants
{

	/** バージョン情報。 */
	const VERSION = '0.0.24';

	/** セッション名。 */
	const SESSION_CORE = 'NUE_Core';

	/** ユーザ追加状態。 */
	const STATE_USER_ADD = 'core/user/add';

	/** ユーザログオン状態。 */
	const STATE_USER_LOGON = 'core/user/logon';

	/** ユーザ設定変更確定状態。 */
	const STATE_USER_MOD = 'core/user/mod';

	/** ユーザ新規作成状態。 */
	const STATE_USER_NEW = 'core/user/new';

	/** ユーザ設定変更状態。 */
	const STATE_USER_PREF = 'core/user/pref';

	/** ブログ表示状態。 */
	const STATE_ARTICLE_VIEW = 'core/article/view';

	/** 検索状態。 */
	const STATE_ARTICLE_FIND = 'core/article/find';

	/** MySQL用のID。 */
	const DBMS_MYSQL = 'mysql';

	/** SQLite用のID。 */
	const DBMS_SQLITE = 'sqlite';

	/** 既定のXSLファイル。 */
	const FILE_XSL_DEFAULT = 'default.xsl';

	/**	ライブラリ フォルダ。 */
	public static $LIB_DIR;

	/**	ルート フォルダ。 */
	public static $ROOT_DIR;

}

CConstants::$LIB_DIR = dirname(__FILE__);
CConstants::$ROOT_DIR = CConstants::$LIB_DIR . '/..';

?>
