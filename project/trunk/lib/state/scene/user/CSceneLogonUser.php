<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	ログオンするシーンです。
 */
class CSceneLogonUser
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	ユーザID。 */
	private $id = '';

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
			self::$instance = new CSceneLogonUser();
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
			if(!(isset($_POST['id']) && isset($_POST['pwd'])) ||
				strlen($_POST['id']) == 0)
			{
				throw new Exception(_('IDを指定しない場合受理不可。'));
			}
			$this->id = $_POST['id'];
			if($entity->connectDatabase())
			{
				$user = new CUser($_POST['id']);
				if(!$user->rollback())
				{
					throw new Exception(_('該当ユーザは存在しない。'));
				}
				$body =& $user->getEntity()->storage();
				$password = $body['password'];
				if(strlen($password) == 0)
				{
					if(strlen($_POST['pwd']) != 0)
					{
						throw new Exception(_('パスワードの不整合。'));
					}
				}
				else
				{
					if($password !== sha1($_POST['pwd']))
					{
						throw new Exception(_('パスワードの不整合。'));
					}
				}
				$entity->startSession();
				$entity->setUser($user);
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
			$query = array();
			if($this->errors !== null)
			{
				$query = array(
					'f' => 'core/user/new',
					'id' => $this->id,
					'err' => $this->errors);
			}
			CRedirector::seeOther($query);
			$entity->dispose();
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
