<?php

require_once('CFileCache.php');

/**
 *	実体SQL用ファイル キャッシュ。
 */
class CFileEntity
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
			self::$instance = new CFileEntity();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
		parent::__construct(NUE_ROOT . '/sql/entitys');
	}

	/**
	 *	不明なプロパティが呼ばれた際に呼び出されます。
	 *
	 *	ここでは、プロパティ名をファイルと見なし呼び出します。
	 *
	 *	@param ファイル名。
	 *	@return ファイル内容文字列。
	 */
	public __get($name)
	{
		return $this->load($name . 'sql');
	}
}

?>
