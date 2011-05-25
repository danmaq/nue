<?php

require_once('CEntity.php');
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
	 *	@return boolean 接続に成功した場合、true。
	 */
	public function connectDatabase()
	{
		$db = CDBManager::getInstance();
		$result = $db->connect();
		if(!$result)
		{
			$db->close();
			$this->setNextState(CSceneDBFailed::getInstance());
		}
		return $result;
	}
}

?>
