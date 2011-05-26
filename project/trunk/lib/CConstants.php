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
	const VERSION = '0.0.17';

	/** ブログ表示状態。 */
	const STATE_VIEW = 'view';

	/** 検索状態。 */
	const STATE_FIND = 'find';

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
