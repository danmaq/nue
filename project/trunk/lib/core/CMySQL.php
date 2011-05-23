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

	/**	最後に発生した例外オブジェクト。 */
	public $exception;

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
		$result = false;
		try
		{
			$dsn = sprintf('mysql:dbname=%s;host=%s;port=%d',
				CConfigure::DB_NAME, CConfigure::DB_HOST, CConfigure::DB_PORT);
			$this->dbo = new PDO($dsn, CConfigure::DB_USER, CConfigure::DB_PASSWORD);
			$result = true;
		}
		catch(Exception $e)
		{
			$this->dbo = null;
			$this->exception = $e;
		}
		return $result;
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
	 *	エラー発生時の例外オブジェクトを取得します。
	 *
	 *	@return Exception 例外オブジェクト。
	 */
	public function getException()
	{
		return $this->exception;
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
		// TODO : 未実装。
		return null;
	}
}

?>
