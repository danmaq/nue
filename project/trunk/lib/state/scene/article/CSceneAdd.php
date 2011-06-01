<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once('CSceneNew.php');

/**
 *	記事を追加するシーンです。
 */
class CSceneAdd
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	既定の値一覧。 */
	private $format = array(
		'caption' => '',
		'description' => '',
	);

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
			self::$instance = new CSceneAdd();
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
				$body =& $user->getEntity()->storage();
				if(!$body['root'])
				{
					throw new Exception(_('管理者以外は投稿不可。'));
				}
				$len = strlen($_POST['caption']);
				if($len < 1 || $len > 255)
				{
					throw new Exception(_('件名は1～255バイト以内。'));
				}
				if(strlen($_POST['description']) <= 2)
				{
					throw new Exception(_('本文なしは受理不可。'));
				}
				$topic = new CTopic();
				$body =& $topic->getEntity()->storage();
				$body['caption'] = htmlspecialchars($_POST['caption'], ENT_COMPAT, 'UTF-8');
				$body['description'] = htmlspecialchars($_POST['description'], ENT_COMPAT, 'UTF-8');
				$body['created_user'] = $user->getEntity()->getID();
				if(!$topic->commit())
				{
					throw new Exception(_('予期しない投稿の失敗。'));
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
					'f' => 'core/article/topic/new',
					'caption' => $_POST['caption'],
					'description' => $_POST['description'],
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
