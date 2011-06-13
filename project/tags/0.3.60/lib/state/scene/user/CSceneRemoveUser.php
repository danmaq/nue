<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once('CSceneLogoffUser.php');

/**
 *	ユーザを削除するシーンです。
 */
class CSceneRemoveUser
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	エラー表示。 */
	private $errors = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneRemoveUser();
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
		try
		{
			if($_SERVER['REQUEST_METHOD'] !== 'POST')
			{
				throw new Exception(_('POSTメソッド以外は受理不可。'));
			}
			if($entity->connectDatabase())
			{
				$entity->startSession();
				$user = $entity->getUser();
				if($user === null)
				{
					throw new Exception(_('ログインしていないため受理不可。'));
				}
				$body =& $user->storage();
				if($body['root'])
				{
					throw new Exception(_('管理者ユーザは削除不可。'));
				}
				if(!$user->delete())
				{
					throw new Exception(_('予期しない理由での削除失敗。'));
				}
				$entity->setUser(null);
				$entity->endSession();
			}
		}
		catch(Exception $e)
		{
			$this->errors = $e->getMessage();
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
			if($this->errors === null)
			{
				$nextState = CSceneLogoffUser::getInstance();
			}
			else
			{
				CRedirector::seeOther(array(
					'f' => 'core/user/pref',
					'err' => $this->errors));
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
