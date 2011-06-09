<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTagCategory.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once('CSceneTagPref.php');

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
		'cat' => false,
		'order' => 0,
	);

	/**	エラー表示。 */
	private $errors = null;

	/**	タグDAOオブジェクト。 */
	private $tag = null;

	/**	親タグDAOオブジェクト。 */
	private $parent = null;

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
					throw new Exception(_('管理者以外は受理不可。'));
				}
				$tag = new CTag($_POST['t']);
				if(!$tag->rollback())
				{
					throw new Exception(_('指定タグは存在しないため受理不可。'));
				}
				$this->tag = $tag;
				$body =& $tag->storage();
				if($body['parent'] !== $_POST['parent'])
				{
					$oParent = new CTag($body['parent']);
					if(strlen($_POST['parent']) > 0)
					{
						$nParent = new CTag($_POST['parent']);
						if(!$nParent->rollback())
						{
							throw new Exception(_('指定の親タグは存在しないため受理不可。'));
						}
						$body['parent'] = $nParent->getID();
						$body =& $nParent->storage();
						array_push($body['childs'], $tag->getID());
						$nParent->commit();
						$tag->commit();
					}
					if($oParent->rollback())
					{
						$body =& $oParent->storage();
						$index = array_search($_POST['t'], $body['childs']);
						if($index !== false)
						{
							array_splice($body['childs'], $index, 1);
						}
						$oParent->commit();
					}
				}

				$category = new CTagCategory($tag->getID());
				$category->rollback();
				if($_POST['cat'])
				{
					$category->order = $_POST['order'];
					$category->commit();
				}
				else
				{
					$category->delete();
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
				$query = array(
					't' => $_POST['t']);
			}
			else
			{
				$query = array(
					'f' => 'core/tag/pref',
					't' => $_POST['t'],
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
