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
	private $dbo;

	/**	最後に発生した例外オブジェクト。 */
	private $exception;

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
	 *	デストラクタ。
	 */
	function __destruct()
	{
		$this->close();
	}

	/**
	 *	接続を確立します。
	 *
	 *	強制的に再接続したい場合、closeメソッドを呼んで一旦解放してください。
	 *
	 *	@return boolean 接続できた場合、true。
	 */
	public function connect()
	{
		$result = ($this->dbo !== null);
		if(!$result)
		{
			try
			{
				$dsn = sprintf('mysql:dbname=%s;host=%s;port=%d;charset=utf8',
					CConfigure::DB_NAME, CConfigure::DB_HOST, CConfigure::DB_PORT);
				$this->dbo = new PDO($dsn, CConfigure::DB_USER, CConfigure::DB_PASSWORD);
				$this->dbo->query('SET NAMES utf8;');
				$result = true;
			}
			catch(Exception $e)
			{
				$this->dbo = null;
				$this->exception = $e;
			}
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
			$result = $this->bind($sql, $args)->execute();
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
			$stmt = $this->bind($sql, $args);
			if($stmt->execute())
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
			throw $e;
		}
		return $result;
	}

	/**
	 *	データベースにSQLを実行させ、単一の値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $column 対象の列。
	 *	@param string $args 引数一覧。
	 *	@return mixed 値。
	 */
	public function singleFetch($sql, $column, $args = array())
	{
		$body = $this->execAndFetch($sql, $args);
		if($body == null)
		{
			throw $this->getException();
		}
		return $body[0][$column];
	}

	/**
	 *	SQLにパラメータを設定します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param string $args 引数一覧。
	 *	@return PDOStatement PreparedStatement オブジェクト。
	 */
	private function bind($sql, $args = array())
	{
		$stmt = $this->getPDO()->prepare($sql);
		if(!$stmt)
		{
			error_log($sql);
			throw new Exception($stmt);
		}
		$obsolete = false;
		foreach(array_keys($args) as $item)
		{
			$name = ':' . $item;
			if(is_array($args[$item]))
			{
				$stmt->bindParam($name, $args[$item][0],
					$args[$item][0] === null ? PDO::PARAM_NULL : $args[$item][1]);
			}
			else
			{
				$stmt->bindParam($name, $args[$item]);
				$obsolete = true;
			}
		}
		if($obsolete)
		{
			error_log(sprintf("[NUE]Using default type is not recommended:\n%s\n%s",
				$sql, print_r($args, true)));
		}
		return $stmt;
	}
}

?>
