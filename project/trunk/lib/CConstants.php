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
	const VERSION = '0.0.21';

	/** セッション名。 */
	const SESSION_CORE = 'NUE_Core';

	/** ユーザ追加状態。 */
	const STATE_USER_NEW = 'core/user/new';

	/** ユーザ追加状態。 */
	const STATE_USER_ADD = 'core/user/add';

	/** ブログ表示状態。 */
	const STATE_VIEW = 'core/view';

	/** 検索状態。 */
	const STATE_FIND = 'core/find';

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
