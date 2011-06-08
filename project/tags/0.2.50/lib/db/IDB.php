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
	 *	データベースにSQLを実行させます。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $args 引数一覧。
	 *	@return boolean 成功した場合、true。
	 */
	function execute($sql, $args = array());

	/**
	 *	データベースにSQLを実行させ、値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $args 引数一覧。
	 *	@return array 値一覧。
	 */
	function execAndFetch($sql, $args = array());

	/**
	 *	データベースにSQLを実行させ、単一の値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $column 対象の列。
	 *	@param string $args 引数一覧。
	 *	@return mixed 値。
	 */
	function singleFetch($sql, $column, $args = array());
}

?>
