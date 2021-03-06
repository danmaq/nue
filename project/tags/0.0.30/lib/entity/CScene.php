<?php

require_once(NUE_CONSTANTS);
require_once('CEntity.php');
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneSimpleError.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneDBFailed.php');

/**
 *	状態を持ったオブジェクト。
 */
class CScene
	extends CEntity
{

	/**	現在セッション中かどうか。 */
	private static $inSession = false;

	/**
	 *	コンストラクタ。
	 *
	 *	@param $firstState 最初の状態。既定ではnull。
	 */
	public function __construct(IState $firstState = null)
	{
		parent::__construct($firstState);
	}

	/**
	 *	データベースに接続します。
	 *
	 *	失敗した場合、自動的にエラーメッセージを表示するシーンへとジャンプします。
	 *	シーンのコミットは行われないため、明示的に行うか、現在の状態を1ループ実行する必要があります。
	 *
	 *	@return boolean 接続に成功した場合、true。
	 */
	public function connectDatabase()
	{
		$db = CDBManager::getInstance();
		$result = $db->connect();
		if(!$result)
		{
			$this->setNextState(CSceneDBFailed::getInstance());
		}
		return $result;
	}

	/**
	 *	セッションを開始します。
	 *
	 *	失敗した場合、自動的にエラーメッセージを表示するシーンへとジャンプします。
	 *	シーンのコミットは行われないため、明示的に行うか、現在の状態を1ループ実行する必要があります。
	 *
	 *	@param $name セッション名。
	 *	@return boolean セッション開始に成功した場合、true。
	 */
	public function startSession($name = CConstants::SESSION_CORE)
	{
		if(!self::$inSession)
		{
			self::$inSession = true;
			try
			{
				session_name($name);
				session_start();
			}
			catch(Exception $e)
			{
				error_log($e);
				unset($_SESSION['user']);
			}
		}
	}

	public function endSession()
	{
		session_write_close();
		self::$inSession = false;
	}

	/**
	 *	ユーザ情報をセッションに格納します。
	 *
	 *	注意: セッションの保存とクローズは自動で行われません。
	 *
	 *	@param CUser $value ユーザ情報。nullを設定すると自動的にセッションから削除します。
	 */
	public function setUser(CUser $value = null)
	{
		if($value === null)
		{
			unset($_SESSION['user']);
		}
		else
		{
			$_SESSION['user'] = $value;
		}
	}

	/**
	 *	セッションに保存されたユーザ情報を取得します。
	 *
	 *	@param IState $stateOnFailed 失敗時の状態。
	 *	@return CUser ユーザ情報。
	 */
	public function getUser(IState $stateOnFailed = null)
	{
		$result = null;
		if(isset($_SESSION['user']) && $_SESSION['user'] instanceof CUser)
		{
			$result = $_SESSION['user'];
		}
		else
		{
			$this->setNextState($stateOnFailed);
		}
		return $result;
	}
}

?>
