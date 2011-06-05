<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	ユーザを追加するシーンです。
 */
class CSceneAddUser
	implements IState
{

	/**	有効なIDの最大長(バイト単位)。 */
	const VALIDATE_ID_LENGTH = 255;

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	エラー一覧。 */
	private $errors = null;

	/**	ユーザID。 */
	private $id = '';

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneAddUser();
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
			if(!isset($_POST['id']) || strlen($_POST['id']) == 0)
			{
				throw new Exception(_('IDを指定しない場合受理不可。'));
			}
			$idlen = strlen($_POST['id']);
			$this->id = $_POST['id'];
			if($idlen > self::VALIDATE_ID_LENGTH)
			{
				throw new Exception(
					sprintf(_('%dバイトを超えるIDは受理不可。'), self::VALIDATE_ID_LENGTH));
			}
			if(!preg_match("/^[\x20-\x7E]+$/", $this->id))
			{
				throw new Exception(_('ASCII文字以外は受理不可。'));
			}
			if($entity->connectDatabase())
			{
				$entity->startSession();
				$user = new CUser($this->id);
				if(CUser::getTotalCount() > 0 && $user->rollback())
				{
					throw new Exception(_('存在するユーザIDは受理不可。'));
				}
				$body =& $user->getEntity()->storage();
				$body['root'] = CUser::getTotalCount() == 0;
				$body['name'] = trim($this->id);
				if($user->commit())
				{
					$entity->setUser($user);
				}
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
			if($this->errors === null)
			{
				$query = array('f' => 'core/user/pref');
			}
			else
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
