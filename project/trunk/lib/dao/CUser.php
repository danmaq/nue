<?php

/**
 *	ユーザDAOクラス。
 */
class CUser
	implements IDB
{

	/**	ユーザ数。 */
	private static $users = -1;

	/**
	 *	ユーザ数を取得します。
	 *
	 *	@return integer ユーザ数。
	 */
	public static function getUserCount()
	{
		if(self::$users < 0)
		{
			$db = CDBManager->getInstance();
		}
		return self::$users;
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
