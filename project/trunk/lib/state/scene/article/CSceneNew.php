<?php

require_once(NUE_CONSTANTS);
require_once('CSceneBlank.php');

/**
 *	記事を作成するシーンです。
 */
class CSceneNew
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	ユーザ オブジェクト。 */
	private $user = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneNew();
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
				$body =& $user->getEntity()->storage();
				if(!$body["root"])
				{
					$entity->setNextState($sceneBlank);
				}
			}
			$this->user = $user;
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
			$topic = $xmlbuilder->createTopic(_('記事の新規作成'));
			$form = $xmlbuilder->createForm($topic, './');

			$p = $xmlbuilder->createParagraph($form);
			$xmlbuilder->createTextInput($p, 'text', 'caption',
				isset($_GET['caption']) ? $_GET['caption'] : '', _('タイトル'), 1, 255, false);
			$xmlbuilder->createTextArea($p, 'description',
				_('記事内容'), isset($_GET['description']) ? $_GET['description'] : ' ');
			$p = $xmlbuilder->createParagraph($form);
			$xmlbuilder->createHTMLElement($p, 'input', array(
				'type' => 'hidden',
				'name' => 'f',
				'value' => 'core/article/topic/post'));
			$xmlbuilder->createHTMLElement($p, 'input', array(
				'type' => 'submit',
				'value' => _('投稿')));
			if(isset($_GET['err']))
			{
				$p = $xmlbuilder->createParagraph($form, _('エラー'));
				$xmlbuilder->addText($p, $_GET['err']);
			}
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
