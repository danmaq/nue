<?php

require_once(dirname(__FILE__) . '/../CConstants.php');
require_once('IDB.php');

/**
 *	MySQL専用のデータベース接続するクラス。
 */
class CMySQL
	implements IDB
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	データベース オブジェクト。 */
	public $dbo;

	/**
	 *	データベース オブジェクトを取得します。
	 *
	 *	@return IDB データベース オブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CMySQL();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
	}

	/**
	 *	接続を確立します。
	 *
	 *	@return boolean 接続できた場合、true。
	 */
	public function connect()
	{
		$dsn = sprintf('mysql:dbname=%s;host=%s;port=%d',
			CConfigure::DB_NAME, CConfigure::DB_HOST, CConfigure::DB_PORT);
		$this->dbo = new PDO($dsn, CConfigure::DB_USER, CConfigure::DB_PASSWORD);
		// TODO : 接続できたかどうかをここで検証して、booleanで返す。
		return true;
	}

	/**
	 *	PDOオブジェクトを取得します。
	 *
	 *	@return PDO PDOオブジェクト。
	 */
	public function getPDO()
	{
		return $this->dbo;
	}

	/**
	 *	接続を閉じます。
	 */
	public function close()
	{
		if($this->$dbo !== null)
		{
			$this->dbo->commmit();
			$this->dbo = null;
		}
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
		return null;
	}
}

?>
