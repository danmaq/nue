<?php

require_once('CFileCache.php');

/**
 *	実体SQL用ファイルキャッシュ。
 */
class CFileSQLEntity
	extends CFileCache
{

	/**	ルート フォルダ。 */
	private static $rootdir = NUE_ROOT . '/sql/entity';

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
			self::$createUUID = new CFileEntity(self::$rootdir . '/createUUID.sql');
		}
		return self::$createUUID;
	}

	/**
	 *	テーブル定義SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getDDL()
	{
		if(self::$ddl == null)
		{
			self::$ddl = new CFileEntity(self::$rootdir . '/ddl.sql');
		}
		return self::$ddl;
	}

	/**
	 *	レコード削除SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getDelete()
	{
		if(self::$delete == null)
		{
			self::$delete = new CFileEntity(self::$rootdir . '/delete.sql');
		}
		return self::$delete;
	}

	/**
	 *	レコード挿入SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getInsert()
	{
		if(self::$insert == null)
		{
			self::$insert = new CFileEntity(self::$rootdir . '/insert.sql');
		}
		return self::$insert;
	}

	/**
	 *	レコード更新SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getUpdate()
	{
		if(self::$update == null)
		{
			self::$update = new CFileEntity(self::$rootdir . '/update.sql');
		}
		return self::$update;
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
