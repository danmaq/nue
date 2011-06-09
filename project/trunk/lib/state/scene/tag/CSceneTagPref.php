<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CTagCategory.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/view/CRedirector.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	タグ設定のシーンです。
 */
class CSceneTagPref
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	タグDAOオブジェクト。 */
	private $tag = null;

	/**	カテゴリタグとして指定されているかどうか。 */
	private $category = false;

	/**	カテゴリ並び順。 */
	private $order = 0;

	/**	親タグ名。 */
	private $parent = '';

	/**	エラー表示。 */
	private $errors = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneTagPref();
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
			try
			{
				$tag = null;
				if(isset($_GET['t']) && strlen($_GET['t']) > 0)
				{
					$t = new CTag($_GET['t']);
					if($t->rollback())
					{
						$tag = $t;
					}
				}
				$this->tag = $tag;
				if($tag == null)
				{
					throw new Exception(_('指定タグは存在しない。'));
				}
				$entity->startSession();
				$user = $entity->getUser();
				if($user === null)
				{
					throw new Exception(_('ログインしていないユーザは編集不可。'));
				}
				$body =& $user->storage();
				if(!$body['root'])
				{
					throw new Exception(_('管理者以外は編集不可。'));
				}
				$this->user = $user;
				$body =& $tag->storage();
				$this->parent = $body['parent'];
				$category = new CTagCategory($_GET['t']);
				$this->category = $category->rollback();
				$this->order = $category->order;
			}
			catch(Exception $e)
			{
				$this->errors = $e->getMessage();
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
			$err = $this->errors;
			$tag = $this->tag;
			if($err === null)
			{
				$xmlbuilder = new CDocumentBuilder(_('PREFERENCE'));
				$xmlbuilder->createUserLogonInfo($this->user, false);
				$topic = $xmlbuilder->createTopic(sprintf(_('タグ %s の編集'), $tag->getID()));
				$form = $xmlbuilder->createForm($topic, './');
				$p = $xmlbuilder->createParagraph($form);
				$xmlbuilder->createHTMLElement($p, 'a', array('href' => '?f=core/tag/all'),
					_('全タグ一覧表示'));
				$p = $xmlbuilder->createParagraph($form);
				$xmlbuilder->createTextInput($p, 'text', 'parent', $this->parent, _('親タグ'),
					0, 255);
				$xmlbuilder->createCheckInput($p, 'cat', $this->category, _('カテゴリ'));
				$xmlbuilder->createTextInput($p, 'text', 'order', $this->order, _('並び順'),
					1, 4, 0);

				$p = $xmlbuilder->createParagraph($form);
				$xmlbuilder->createHiddenInput($p, 'f', 'core/tag/mod');
				$xmlbuilder->createHiddenInput($p, 't', $tag->getID());
				$xmlbuilder->createSubmitInput($p, _('編集'));

				$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
			}
			else
			{
				$query = $tag === null ? array() : array('t' => $tag->getID());
				CRedirector::seeOther($query);
			}
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
