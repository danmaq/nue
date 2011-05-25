<?php

require_once('CFileCache.php');

/**
 *	実体SQL用ファイルキャッシュ。
 */
class CFileEntity
{

	/**	ルート フォルダ。 */
	private static $rootdir = DIR_ROOT . '/sql/entity';

	/**	UUID取得SQL用クラス オブジェクト。 */
	private static $createUUID = null;

	/**	テーブル定義SQL用クラス オブジェクト。 */
	private static $ddl = null;

	/**	レコード削除SQL用クラス オブジェクト。 */
	private static $delete = null;

	/**	レコード挿入SQL用クラス オブジェクト。 */
	private static $insert = null;

	/**	レコード更新SQL用クラス オブジェクト。 */
	private static $update = null;

	/**
	 *	UUID取得SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getCreateUUID()
	{
		if(self::$createUUID == null)
		{
			self::$createUUID = new CFileEntity\(self::$rootdir . '/createUUID.sql');
		}
		return self::$createUUID;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $fpath ファイル パス。
	 */
	private function __construct($fpath)
	{
		parent::__construct($fpath);
	}
}

?>
