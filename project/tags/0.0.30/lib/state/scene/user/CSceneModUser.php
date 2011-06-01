<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	ユーザ設定を変更するシーンです。
 */
class CSceneModUser
	implements IState
{

	/**	有効なIDの最大長(バイト単位)。 */
	const VALIDATE_ID_LENGTH = 255;

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	既定の値一覧。 */
	private $format = array(
		'name' => '',
		'pwd0' => '',
		'pwd1' => '',
		'pwd2' => '',
	);

	/**	名前。 */
	private $name = null;

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
			self::$instance = new CSceneModUser();
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
				$_POST += $this->format;
				$entity = $user->getEntity();
				$body =& $entity->storage();
				$len = strlen($_POST['name']);
				if($len > 0)
				{
					if($len > 255)
					{
						throw new Exception(_('名前は1～255文字の範囲内以外受理不可。'));
					}
					$this->name = $_POST['name'];
					$body['name'] = $_POST['name'];
				}
				if($_POST['pwd1'] !== $_POST['pwd2'])
				{
					throw new Exception(_('新しいパスワードの不整合。'));
				}
				$len = strlen($_POST['pwd1']);
				if(strlen($body['password']) > 0)
				{
					if($body['password'] !== sha1($_POST['pwd0']))
					{
						throw new Exception(_('現在のパスワードの不整合。'));
					}
				}
				else
				{
					if($len < 4 || $len > 255)
					{
						throw new Exception(_('パスワードは4～255文字の範囲内以外受理不可。'));
					}
				}
				if($len >= 4 && $len <= 255)
				{
					$body['password'] = sha1($_POST['pwd1']);
				}
				if(!$entity->commit())
				{
					throw new Exception(_('予期しない理由でのコミット失敗。'));
				}
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
				$query = array();
			}
			else
			{
				$query = array(
					'f' => CConstants::STATE_USER_PREF,
					'name' => $this->name,
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
