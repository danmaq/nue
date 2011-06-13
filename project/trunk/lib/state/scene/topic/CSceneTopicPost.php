<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once('CSceneTopicNew.php');

/**
 *	記事を追加するシーンです。
 */
class CSceneTopicPost
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	既定の値一覧。 */
	private $format = array(
		'caption' => '',
		'description' => '',
		'uts' => 0,
		'tags' => CConfigure::DEFAULT_TAG
	);

	/**	エラー表示。 */
	private $errors = null;

	/**	記事DAOオブジェクト。 */
	private $topic = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneTopicPost();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
		$format['uts'] = time();
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
				$caption = trim($_POST['caption']);
				$len = strlen($caption);
				if($len < 1 || $len > 255)
				{
					throw new Exception(_('件名は1～255バイト以内。'));
				}
				if(strlen($_POST['description']) <= 2)
				{
					throw new Exception(_('本文なしは受理不可。'));
				}
				$topic = null;
				if(isset($_POST['id']))
				{
					$topic = new CTopic($_POST['id']);
					if(!$topic->rollback())
					{
						$topic = null;
					}
				}
				if($topic === null)
				{
					$topic = new CTopic();
				}
				$uts = strtotime($_POST['uts']);
				$topic->userTimeStamp = ($uts !== false && $uts >= 0) ? $uts : time();
				$body =& $topic->storage();
				$body['caption'] = htmlspecialchars($caption, ENT_COMPAT, 'UTF-8');
				$descs = preg_split('/(\x0d\x0a|\x0d|\x0a){2,}/', trim($_POST['description']), -1, PREG_SPLIT_NO_EMPTY);
				for($i = count($descs); --$i >= 0; )
				{
					$descs[$i] = htmlspecialchars(trim($descs[$i]), ENT_COMPAT, 'UTF-8');
				}
				$body['description'] = serialize($descs);
				$body['created_user'] = $user->getEntity()->getID();
				if(!$topic->commit())
				{
					throw new Exception(_('予期しない投稿の失敗。'));
				}
				$this->topic = $topic;

				$tags = preg_split('/[\s　]+/', trim($_POST['tags']));
				$tags = array_splice($tags, 0, min(count($tags), CConfigure::TAG_MAX));
				$topic->setTagAssignList($tags);
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
				if($this->topic === null)
				{
					$query = array();
				}
				else
				{
					$query = array(
						'f' => 'core/article/topic/view',
						'id' => $this->topic->getID());
				}
			}
			else
			{
				$query = array(
					'f' => 'core/article/topic/new',
					'caption' => $_POST['caption'],
					'description' => $_POST['description'],
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
