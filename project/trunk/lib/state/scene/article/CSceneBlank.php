<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/db/CDBManager.php');
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/scene/initialize/CSceneHello.php');

/**
 *	ブランクな記事表示のシーンです。
 */
class CSceneBlank
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	ユーザ オブジェクト。 */
	private $user = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneBlank();
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
		if($entity->connectDatabase())
		{
			$entity->startSession();
			$this->user = $entity->getUser();
		}
	}

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function execute(CEntity $entity)
	{
		if($entity->getNextState() === null)
		{
			$nextState = CEmptyState::getInstance();
			if(CUser::getUserCount() == 0)
			{
				// 初回表示へ
				$nextState = CSceneHello::getInstance();
			}
			else
			{
				// TODO : 記事がないので作りましょう。
				$xmlbuilder = new CDocumentBuilder();
				if($this->user !== null)
				{
					$xmlbuilder->createUserLogonInfo();
				}
				$xmlbuilder->createSimpleMessage(_('ERROR'), _('記事がありません。'));
				$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
			}
			$entity->setNextState($nextState);
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
