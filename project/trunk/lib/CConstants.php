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
	const VERSION = '0.4.67';

	/** セッション名。 */
	const SESSION_CORE = 'NUE_Core';

	/** MySQL用のID。 */
	const DBMS_MYSQL = 'mysql';

	/** SQLite用のID。 */
	const DBMS_SQLITE = 'sqlite';

	/** 既定のXSLファイル。 */
	const FILE_XSL_DEFAULT = 'default.xsl';

}

?>
