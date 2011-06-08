<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	全タグ一覧表示のシーンです。
 */
class CSceneAllTagList
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
			self::$instance = new CSceneAllTagList();
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
			$xmlbuilder->createSearchInfo();
			$t = $xmlbuilder->createTopic(_('全タグ一覧'));
			$p = $xmlbuilder->createParagraph($t);
			foreach($this->tags as $item)
			{
				$id = $item->getID();
				$xmlbuilder->createHTMLElement($p, 'a',
					array('href' => '?t=' . urlencode($id)), $id);
				$xmlbuilder->addText($p, sprintf(_('(%d件)'), $item->getListFromTagCount()));
				$xmlbuilder->createHTMLElement($p, 'br');
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
