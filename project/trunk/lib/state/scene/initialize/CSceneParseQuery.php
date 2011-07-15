<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneSimpleError.php');

/**
 *	GETクエリをパースするシーン。
 */
class CSceneParseQuery
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	トップページかどうか。 */
	private $top = true;

	/**	プラグイン名。 */
	private $plugin = '';

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneParseQuery();
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
	 *	トップページかどうかを取得します。
	 *
	 *	@return boolean トップページである場合、true。
	 */
	public function isStartPage()
	{
		return $this->top;
	}

	/**
	 *	プラグインのパスを取得します。
	 *
	 *	@return string パス。
	 */
	public function getPluginPath()
	{
		return $this->plugin;
	}

	/**
	 *	この状態が開始されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function setup(CEntity $entity)
	{
		$top = true;
		foreach(array_keys($_GET) as $item)
		{
			$this->top = false;
			$this->parseCategory($item);
			$this->parsePage($item);
			$this->parseGUID($item);
		}
		$this->setQueryIfNotExists('from', 0);
		$this->setQueryIfNotExists('tpp', CConfigure::DEFAULT_TOPIC_PER_PAGE);
		$this->setQueryIfNotExists('skin', CConfigure::SKINSET);
		$existId = isset($_GET['id']);
		if($this->setQueryIfNotExists('f',
			$existId ? 'core/article/topic/view' : 'core/article/view') && !$existId)
		{
			$this->setQueryIfNotExists('t', CConfigure::DEFAULT_TAG);
		}
	}

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function execute(CEntity $entity)
	{
		$nextState = CSceneSimpleError::getIllegalModeInstance();
		$target = NUE_ROOT . preg_replace('/(\.|\/){2,}/', '\1', sprintf('/plugin/%s.php', str_replace(
			"\0", '', $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST['f'] : $_GET['f'])));
		$this->plugin = $target;
		if(file_exists($target))
		{
			require_once($target);
		}
		$entity->setNextState($nextState);
	}

	/**
	 *	別の状態へ移行される直前に呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function teardown(CEntity $entity)
	{
	}

	/**
	 *	GETクエリに該当キーが存在しない場合、値を設定します。
	 *
	 *	@param string $key キー。
	 *	@param string $value 値。
	 *	@return boolean 値を設定できた場合、true。
	 */
	private function setQueryIfNotExists($key, $value)
	{
		$result = !isset($_GET[$key]) || strlen($_GET[$key]) === 0;
		if($result)
		{
			$_GET[$key] = $value;
		}
		return $result;
	}

	/**
	 *	カテゴリ クエリをパースします。
	 *
	 *	@param string $item クエリ文字列。
	 */
	private function parseCategory($item)
	{
		if(preg_match('/^\//', $item))
		{
			if(isset($_GET[$item]))
			{
				unset($_GET[$item]);
			}
			$item = preg_replace('/^\//', '', $this->parsePage($item));
			$_GET['t'] = $this->parsePage($item);
		}
	}

	/**
	 *	ページ範囲指定クエリをパースします。
	 *
	 *	@param string $item クエリ文字列。
	 *	@return ページ範囲指定クエリを削除した文字列。
	 */
	private function parsePage($item)
	{
		$result = array();
		if(preg_match('/^\((\d*)\/(\d*)\)$/', $item, $result))
		{
			if(isset($_GET[$result[0]]))
			{
				unset($_GET[$result[0]]);
			}
			$result[1] = $result[1] ? $result[1] : 0;
			$result[2] = $result[2] ? $result[2] : CConfigure::DEFAULT_TOPIC_PER_PAGE;
			$_GET['from'] = $result[1];
			$_GET['tpp'] = $result[2];
		}
		return $item;
	}

	/**
	 *	記事指定クエリをパースします。
	 *
	 *	@param string $item クエリ文字列。
	 */
	private function parseGUID($item)
	{
		$result = array();
		if(preg_match('/^[a-fA-F\d]{8}-[a-fA-F\d]{4}-[a-fA-F\d]{4}-[a-fA-F\d]{4}-[a-fA-F\d]{12}$/',
			$item, $result))
		{
			$this->setQueryIfNotExists('id', strtoupper($result[0]));
			if(isset($_GET[$item]))
			{
				unset($_GET[$item]);
			}
		}
	}
}

?>
