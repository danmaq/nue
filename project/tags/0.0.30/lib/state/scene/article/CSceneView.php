<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once('CSceneBlank.php');

/**
 *	記事表示のシーンです。
 */
class CSceneView
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
			self::$instance = new CSceneView();
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

			// TODO : 現在は全記事数を取得しているだけ。
			// 指定カテゴリのページを取得する
			if(CTopic::getTotalCount() > 0)
			{
				$user = $this->user;
				$topics = CTopic::getAll();
				$xmlbuilder = new CDocumentBuilder(_('ARTICLES'));
				$xmlbuilder->createUserLogonInfo($user);
				foreach($topics as $item)
				{
					$body =& $item->getEntity()->storage();
					$topic = $xmlbuilder->createTopic($body['caption']);
					$p = $xmlbuilder->createParagraph($topic);
					$xmlbuilder->addText($p, $body['description']);
				}
				if($user !== null)
				{
					$body =& $user->getEntity()->storage();
					if($body['root'])
					{
						$topic = $xmlbuilder->createTopic(_('管理'));
						$p = $xmlbuilder->createParagraph($topic);
						$xmlbuilder->createHTMLElement($p, 'a',
							array('href' => '?f=' . CConstants::STATE_ARTICLE_NEW),
							_('記事作成'));
					}
				}
				$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
			}
			else	// なければ新規記事作成へ遷移
			{
				$nextState = CSceneBlank::getInstance();
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
