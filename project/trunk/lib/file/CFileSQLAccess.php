<?php

require_once('CFileCache.php');

/**
 *	実体SQL用ファイル キャッシュ。
 */
class CFileSQLAccess
	extends CFileCache
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**
	 *	クラス オブジェクトを取得します。
	 *
	 *	@return CFileCache クラス オブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CFileSQLAccess();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	protected function __construct()
	{
		parent::__construct(NUE_ROOT . '/sql/access');
	}

	/**
	 *	不明なプロパティが呼ばれた際に呼び出されます。
	 *
	 *	ここでは、プロパティ名をファイルと見なし呼び出します。
	 *
	 *	@param ファイル名。
	 *	@return ファイル内容文字列。
	 */
	public function __get($name)
	{
		return $this->load($name . '.sql');
	}
}

?>
