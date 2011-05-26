<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once('CSceneBlank.php');

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
		if($entity->connectDatabase())
		{
			if(false)	// TODO : 指定カテゴリのページを取得する
			{
				// TODO : ページ表示
				$entity->setNextState(CEmptyState::getInstance());
			}
			else	// なければ新規記事作成へ遷移
			{
				$entity->setNextState(CSceneBlank::getInstance());
			}
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
}

?>
