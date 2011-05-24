<?php

require_once(NUE_CONSTANTS);
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
		$this->close();
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
		if($this->dbo !== null)
		{
			$this->dbo->commmit();
			$this->dbo = null;
		}
	}

	/**
	 *	データベースにSQLを実行させます。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $args 引数一覧。
	 *	@return boolean 成功した場合、true。
	 */
	public function execute($sql, $args = array())
	{
		$result = false;
		try
		{
			$result = $this->getPDO()->prepare($sql)->execute($args);
		}
		catch(Exception $e)
		{
			$this->exception = $e;
		}
		return $result;
	}

	/**
	 *	データベースにSQLを実行させ、値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $args 引数一覧。
	 *	@return stdClass 値一覧。
	 */
	public function execAndFetch($sql, $args = array())
	{
		$result = null;
		try
		{
			$stmt = $this->getPDO()->prepare($sql);
			if($stmt->execute($args))
			{
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			else
			{
				$err = $stmt->errorInfo();
				throw new Exception($err[2], $err[0]);
			}
		}
		catch(Exception $e)
		{
			$this->exception = $e;
		}
		return $result;
	}
}

?>
