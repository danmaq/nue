<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTag.php');
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/util/CPager.php');
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

	/**	ユーザDAOオブジェクト。 */
	private $user = null;

	/**	タグDAOオブジェクト。 */
	private $tag = null;

	/**	記事DAO一覧。 */
	private $topics = array();

	/**	ページャ オブジェクト。 */
	private $pager;

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
		$pager = new CPager();
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
			if(isset($_GET['t']) && strlen($_GET['t']) > 0)
			{
				$this->tag = $_GET['t'];
				$tag = new CTag($_GET['t']);
				if($tag->rollback())
				{
					$topics = array();
					$pager = new CPager($_GET['from'], $_GET['tpp']);
					$this->pager = $pager;
					foreach($tag->getListFromTag(false, $pager) as $item)
					{
						array_push($topics, $item->getTopic());
					}
					$this->topics = $topics;
				}
			}
			else
			{
				$this->topics = CTopic::getAll();
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
			$rootPage = CSceneParseQuery::getInstance()->isStartPage();
			$topics = $this->topics;

			// TODO : 現在は全記事数を取得しているだけ。
			// 指定カテゴリのページを取得する
			if(CTopic::getTotalCount() > 0 || !(count($topics) == 0 && $rootPage))
			{
				$user = $this->user;
				$tag = $this->tag;
				$xmlbuilder = $rootPage ?
					new CDocumentBuilder() :
					new CDocumentBuilder(
						$tag === null ? _('ARTICLES') : sprintf(_('TAG: %s'), $tag));
				$xmlbuilder->createUserLogonInfo($user);
				$xmlbuilder->createSearchInfo($rootPage ? null : $tag);
				$xmlbuilder->createCategoryList();
				if(count($topics) === 0)
				{
					$topic = $xmlbuilder->createTopic(
						_('そのタグはどの記事にも使用されていない。'));
					$p = $xmlbuilder->createParagraph($topic);
				}
				else
				{
					$xmlbuilder->createPagerInfo($this->pager);
					foreach($topics as $item)
					{
						$xmlbuilder->createTopic($item);
					}
				}
				if($user !== null)
				{
					$body =& $user->storage();
					if($body['root'])
					{
						$topic = $xmlbuilder->createTopic(_('管理'));
						$p = $xmlbuilder->createParagraph($topic);
						$xmlbuilder->createHTMLElement($p, 'a',
							array('href' => '?f=core/article/topic/new'),
							_('記事作成'));
						if($tag !== null && count($topics) > 0)
						{
							$xmlbuilder->createHTMLElement($p, 'br');
							$xmlbuilder->createHTMLElement($p, 'a',
								array('href' => ('?f=core/tag/pref&amp;t=' . urlencode($tag))),
								sprintf(_('%s タグの設定'), $tag));
						}
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
