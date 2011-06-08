<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	タグ一覧表示のシーンです。
 */
class CSceneTagList
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	タグ一覧。 */
	private $tags = array();

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneTagList();
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
		if($entity->connectDatabase())
		{
			$entity->startSession();
			$this->user = $entity->getUser();
			$this->tags = CTag::getAll(false);
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
			$xmlbuilder = new CDocumentBuilder(_('TAGS'));
			$xmlbuilder->createUserLogonInfo($this->user);
			$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
			if(false)
			{
				$user = $this->user;
				$topic = $this->topic;
				$body =& $topic->storage();
				$xmlbuilder = new CDocumentBuilder(_('TOPIC'));
				$xmlbuilder->createUserLogonInfo($user);
				$t = $xmlbuilder->createTopic($body['caption']);
				foreach($topic->getDescription() as $item)
				{
					$p = $xmlbuilder->createParagraph($t);
					$xmlbuilder->addHLML($p, $item);
				}

				$t = $xmlbuilder->createTopic(_('タグ'));
				$p = $xmlbuilder->createParagraph($t);
				foreach($topic->getTagAssignList() as $item)
				{
					$xmlbuilder->createHTMLElement($p, 'a', array('href' => '#'),
						$item->getTag()->getID());
					$xmlbuilder->createHTMLElement($p, 'br');
				}

				$t = $xmlbuilder->createTopic(_('この記事について'));
				$p = $xmlbuilder->createParagraph($t);
				$xmlbuilder->addText(
					$p, _('投稿日: ') . date('Y/m/d H:i', $topic->getEntity()->getUpdated()));

				$p = $xmlbuilder->createParagraph($t);
				$xmlbuilder->addText(
					$p, _('投稿者: ') . $this->author);
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
