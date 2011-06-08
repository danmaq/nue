<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once('CSceneTopicView.php');

/**
 *	記事を削除するシーンです。
 */
class CSceneTopicRemove
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	記事ID。 */
	private $id = null;

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
			self::$instance = new CSceneTopicRemove();
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
			if($entity->connectDatabase())
			{
				$entity->startSession();
				$user = $entity->getUser();
				if($user === null)
				{
					throw new Exception(_('ログインしていないため受理不可。'));
				}
				$body =& $user->storage();
				if(!$body['root'])
				{
					throw new Exception(_('管理者以外からの削除は受理不可。'));
				}
				if(!isset($_GET['id']))
				{
					throw new Exception(_('対象不明の削除は受理不可。'));
				}
				$this->id = $_GET['id'];
				$topic = null;
				if(isset($_GET['id']))
				{
					$topic = new CTopic($_GET['id']);
					if(!$topic->rollback())
					{
						$topic = null;
					}
				}
				if($topic === null)
				{
					throw new Exception(_('対象IDの記事が見つかりません。'));
				}
				if(!$topic->delete())
				{
					throw new Exception(_('予期しない削除の失敗。'));
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
			if($this->errors === null || $this->id === null)
			{
				$query = array();
			}
			else
			{
				$query = array(
					'f' => 'core/article/topic/view',
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
