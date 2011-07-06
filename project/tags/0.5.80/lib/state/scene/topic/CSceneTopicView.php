<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/scene/article/CSceneView.php');

/**
 *	記事表示のシーンです。
 */
class CSceneTopicView
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	ユーザDAOオブジェクト。 */
	private $user = null;

	/**	記事DAOオブジェクト。 */
	private $topic = null;

	/**	投稿者名。 */
	private $author;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneTopicView();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
		$this->author = _('名無しさん');
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
			if(isset($_GET['id']))
			{
				$topic = new CTopic($_GET['id']);
				if($topic->rollback())
				{
					$author = $topic->getCreatedUser();
					if($author !== null)
					{
						$body =& $author->storage();
						$this->author = $body['name'];
					}
					$this->topic = $topic;
				}
			}
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
			if($this->topic === null)
			{
				$nextState = CSceneView::getInstance();
			}
			else
			{
				$user = $this->user;
				$topic = $this->topic;
				$body =& $topic->storage();
				$xmlbuilder = new CDocumentBuilder(_('TOPIC'));
				$xmlbuilder->createUserLogonInfo($user);
				$xmlbuilder->createSearchInfo();
				$xmlbuilder->createCategoryList();

				$t = $xmlbuilder->createTopic(_('この記事について'));
				$dom = $xmlbuilder->getDOM();
				$ul = $dom->createElement('ul');
				$t->appendChild($ul);
				$li = $dom->createElement('li');
				$ul->appendChild($li);
				$xmlbuilder->addText($li, _('投稿日: ') . date('Y/m/d H:i', $topic->userTimeStamp));
				$li = $dom->createElement('li');
				$ul->appendChild($li);
				$xmlbuilder->addText($li, _('更新日: ') . date('Y/m/d H:i',
					$topic->getEntity()->getUpdated()));
				$li = $dom->createElement('li');
				$ul->appendChild($li);
				$xmlbuilder->addText($li, _('投稿者: ') . $this->author);
				$p = $xmlbuilder->createParagraph($t, _('タグ'));
				$xmlbuilder->addText($p, ' | ');
				foreach($topic->getTagAssignList() as $item)
				{
					$id = $item->getTag()->getID();
					$xmlbuilder->createHTMLElement($p, 'a',
						array('href' => '?t=' . urlencode($id)), $id);
					$xmlbuilder->addText($p, ' | ');
				}

				$xmlbuilder->createTopic($topic);

				if($user !== null)
				{
					$body =& $user->storage();
					if($body['root'])
					{
						$t = $xmlbuilder->createTopic(_('管理'));
						$p = $xmlbuilder->createParagraph($t);
						$xmlbuilder->createHTMLElement($p, 'a',
							array('href' => '?f=core/article/topic/new&amp;id=' . $topic->getID()),
							_('記事編集'));
						$xmlbuilder->addText($p, ' | ');
						$xmlbuilder->createHTMLElement($p, 'a',
							array('href' => '?f=core/article/topic/remove&amp;id=' . $topic->getID()),
							_('記事削除'));
						$xmlbuilder->addText($p, ' | ');
						$xmlbuilder->createHTMLElement($p, 'a',
							array('href' => '?f=core/article/topic/new'), _('記事新規作成'));
					}
				}
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
