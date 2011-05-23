<?php

require_once(dirname(__FILE__) . '/../CConstants.php');
require_once('IDB.php');

/**
 *	データベース オブジェクト管理クラス。
 */
class CDBManager
	implements IDB
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	データベース オブジェクト。 */
	private $db;

	/**
	 *	データベース オブジェクトを取得します。
	 *
	 *	@return IDB データベース オブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CDBManager();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
		$db = null;
		require_once(CConfigure::DB_TYPE . '.php');
		$this->db = $db;
	}

	/**
	 *	接続を確立します。
	 *
	 *	@return boolean 接続できた場合、true。
	 */
	public function connect()
	{
		return $this->db->connect();
	}

	/**
	 *	PDOオブジェクトを取得します。
	 *
	 *	@return mixed PDOオブジェクト。
	 */
	public function getPDO()
	{
		return $this->db->getPDO();
	}

	/**
	 *	接続を閉じます。
	 */
	public function close()
	{
		$this->db->close();
	}

	/**
	 *	データベースから値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param integer $limit 取得する件数。省略時は(2^31)-1件。
	 *	@return mixed 値一覧。
	 */
	public function get($sql, $limit = PHP_INT_MAX)
	{
		return $this->db->get($sql, $limit);
	}
}

?>
