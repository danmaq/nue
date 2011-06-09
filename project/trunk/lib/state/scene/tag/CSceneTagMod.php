<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once('CSceneTopicNew.php');

/**
 *	タグの編集確定シーンです。
 */
class CSceneTagMod
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	既定の値一覧。 */
	private $format = array(
		't' => '',
		'parent' => '',
		'category' => false,
	);

	/**	エラー表示。 */
	private $errors = null;

	private $tag

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneTagMod();
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
				$body =& $user->storage();
				if(!$body['root'])
				{
					throw new Exception(_('管理者以外は投稿不可。'));
				}
				
			}
		}
		catch(Exception $e)
		{
			$this->errors = $e->__toString();
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
				if($this->topic === null)
				{
					$query = array();
				}
				else
				{
					$query = array(
						't' => '');
				}
			}
			else
			{
				$query = array(
					'f' => 'core/tag/pref',
					't' => '',
					'err' => $this->errors);
				if($this->topic !== null)
				{
					$query += array('id' => $this->topic->getID());
				}
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
