<?php

/**
 *	データベース接続するクラスのインターフェイス。
 */
interface IDB
{

	/**
	 *	接続を確立します。
	 */
	function connect();

	/**
	 *	PDOオブジェクトを取得します。
	 *
	 *	@return mixed PDOオブジェクト。
	 */
	function getPDO();

	/**
	 *	接続を閉じます。
	 */
	function close();

	/**
	 *	データベースから値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param integer $limit 取得する件数。省略時は(2^31)-1件。
	 *	@return mixed 値一覧。
	 */
	function get($sql, $limit = PHP_INT_MAX);
}

?>
