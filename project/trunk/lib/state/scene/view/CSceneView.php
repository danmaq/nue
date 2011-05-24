<?php

require_once(NUE_LIB_ROOT . '/db/CDBManager.php');
require_once(NUE_LIB_ROOT . '/CConstants.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneDBFailed.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneSimpleError.php');

/**
 *	記事表示のシーンです。
 */
class CSceneView
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneView();
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
	 *	この状態が開始されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function setup(CEntity $entity)
	{
	}

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function execute(CEntity $entity)
	{
		if($this->connect($entity))
		{
			$xmlbuilder = new CDocumentBuilder();
			$xmlbuilder->createSimpleMessage(_('ERROR'), _('記事がありません。'));
			$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
			$entity->setNextState(CEmptyState::getInstance());
		}
	}

	/**
	 *	別の状態へ移行される直前に呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function teardown(CEntity $entity)
	{
	}

	/**
	 *	データベースに接続します。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 *	@return boolean 接続に成功した場合、true。
	 */
	private function connect(CEntity $entity)
	{
		$db = CDBManager::getInstance();
		$result = $db->connect();
		if(!$result)
		{
			$db->close();
			$entity->setNextState(CSceneDBFailed::getInstance());
		}
		return $result;
	}
}

?>
