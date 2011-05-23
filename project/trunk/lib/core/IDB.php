<?php

/**
 *	データベース接続するクラスのインターフェイス。
 */
interface IDB
{

	/**
	 *	接続を確立します。
	 *
	 *	@return boolean 接続できた場合、true。
	 */
	function connect();

	/**
	 *	PDOオブジェクトを取得します。
	 *
	 *	@return mixed PDOオブジェクト。
	 */
	function getPDO();

	/**
	 *	エラー発生時の例外オブジェクトを取得します。
	 *
	 *	@return Exception 例外オブジェクト。
	 */
	function getException();

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
