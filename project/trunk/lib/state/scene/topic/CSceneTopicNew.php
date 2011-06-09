<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/state/scene/article/CSceneBlank.php');

/**
 *	記事を作成するシーンです。
 */
class CSceneTopicNew
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	ユーザDAOオブジェクト。 */
	private $user = null;

	/**	記事ID。 */
	private $id = null;

	/**	表題。 */
	private $caption = '';

	/**	内容。 */
	private $description = ' ';

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
			self::$instance = new CSceneTopicNew();
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
			$sceneBlank = CSceneBlank::getInstance();
			$user = $entity->getUser($sceneBlank);
			if($user !== null)
			{
				$this->user = $user;
				$body =& $user->storage();
				if(!$body["root"])
				{
					$entity->setNextState($sceneBlank);
				}
				$tags = array();
				if(isset($_GET['id']))
				{
					$topic = new CTopic($_GET['id']);
					if($topic->rollback())
					{
						$body =& $topic->storage();
						$this->id = $_GET['id'];
						$this->caption = $body['caption'];
						$this->description = join("\n\n", $topic->getDescription());
						$tags = $topic->getTagAssignList();
					}
				}
				while(count($tags) < CConfigure::TAG_MAX)
				{
					array_unshift($tags, null);
				}
				$this->tags = $tags;
				if(isset($_GET['caption']))
				{
					$this->caption = $_GET['caption'];
				}
				if(isset($_GET['description']))
				{
					$this->description = $_GET['description'];
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
			$xmlbuilder = new CDocumentBuilder(_('POST'));
			$xmlbuilder->createUserLogonInfo($this->user, false);
			$newtopic = ($this->id === null);
			$topic = $xmlbuilder->createTopic(
				$newtopic ? _('記事の新規作成') : _('記事の編集'));
			$form = $xmlbuilder->createForm($topic, './');

			$p = $xmlbuilder->createParagraph($form);
			$xmlbuilder->createTextInput($p, 'text', 'caption',
				$this->caption, _('タイトル'), 1, 255);
			$xmlbuilder->createTextArea($p, 'description',
				_('記事内容'), $this->description);

			$p = $xmlbuilder->createParagraph($form, _('タグ'));
			$tags = $this->tags;
			for($i = count($tags); --$i >= 0; )
			{
				$mtag = $tags[$i];
				$value = $mtag === null ?
					($i == 0 && $newtopic ? CConfigure::DEFAULT_TAG : '') :
					$mtag->getTag()->getID();
				$xmlbuilder->createTextInput($p, 'text', 'tag_' . $i, $value,
					sprintf('%s %02d', _('タグ'), CConfigure::TAG_MAX - $i), 1, 255);
			}

			$p = $xmlbuilder->createParagraph($form);
			if($this->id !== null)
			{
				$xmlbuilder->createHiddenInput($p, 'id', $this->id);
			}
			$xmlbuilder->createHiddenInput($p, 'f', 'core/article/topic/post');
			$xmlbuilder->createSubmitInput($p, _('投稿'));
			$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
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
