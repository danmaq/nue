<?php

require_once(NUE_CONSTANTS);
require_once('CEntity.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneSimpleError.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneDBFailed.php');

/**
 *	状態を持ったオブジェクト。
 */
class CScene
	extends CEntity
{

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
		session_name($name);
		$result = session_start();
		if(!$result)
		{
			$this->setNextState(CSceneSimpleError::getSessionFailedInstance());
		}
		return $result;
	}
}

?>
