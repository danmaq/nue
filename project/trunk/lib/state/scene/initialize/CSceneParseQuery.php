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
	 *	この状態が開始されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function setup(CEntity $entity)
	{
		$this->setQueryIfNotExists('cat', CConfigure::DEFAULT_CATEGORY);
		$this->setQueryIfNotExists('from', 0);
		$this->setQueryIfNotExists('to', 100);
		$keys = array_keys($_GET);
		foreach ($keys as $item)
		{
			$this->parseCategory($item);
			$this->parsePage($item);
			$this->parseGUID($item);
		}
		$this->setQueryIfNotExists('f', isset($_GET['q']) || isset($_GET['t']) ?
			CConstants::STATE_FIND : CConstants::STATE_VIEW);
	}

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function execute(CEntity $entity)
	{
		$target = dirname(__FILE__) . '/../mode/' . $_GET['f'] . '.php';
		$nextState = CSceneSimpleError::getIllegalModeInstance();
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
	 */
	private function setQueryIfNotExists($key, $value)
	{
		if(!isset($_GET[$key]))
		{
			$_GET[$key] = $value;
		}
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
			$_GET['cat'] = $this->parsePage($item);
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
		if(preg_match('/\((\d*)-(\d*)\)$/', $item, $result))
		{
			if(isset($_GET[$result[0]]))
			{
				unset($_GET[$result[0]]);
			}
			$item = preg_replace('/\((\d*)-(\d*)\)$/', '', $item);
			$result[1] = $result[1] ? $result[1] : 0;
			$result[2] = $result[2] ? $result[2] : 100;
			$rev = $result[1] > $result[2];
			$_GET['from'] = $rev ? $result[2] : $result[1];
			$_GET['to'] = $rev ? $result[1] : $result[2];
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
		if(preg_match('/^[a-fA-F\d]{8}-[a-fA-F\d]{4}-[a-fA-F\d]{4}-[a-fA-F\d]{4}-[a-fA-F\d]{12}$/', $item, $result))
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
