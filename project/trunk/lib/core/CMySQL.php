<?php

require_once('IDB.php');

/**
 *	MySQL専用のデータベース接続するクラス。
 */
class CMySQL
	implements IDB
{

	/**	ホスト名。 */
	public $host;
	/**
	 *	コンストラクタ。
	 */
	public function __construct(string $host, integer $port, string $userId, string $password)
	{
		
	}

	/**
	 *	接続を確立します。
	 */
	public function connect()
	{
	}

	/**
	 *	PDOオブジェクトを取得します。
	 *
	 *	@return mixed PDOオブジェクト。
	 */
	public function getDBO()
	{
	}

	/**
	 *	接続を閉じます。
	 */
	public function close()
	{
	}

	/**
	 *	データベースから値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param integer $limit 取得する件数。省略時は(2^31)-1件。
	 *	@return mixed 値一覧。
	 */
	public function get(string $sql, integer $limit = PHP_INT_MAX)
	{
	}

}

?>
