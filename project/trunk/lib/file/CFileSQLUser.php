<?php

require_once('CFileCache.php');

/**
 *	ユーザ インデックスSQL用ファイル キャッシュ。
 */
class CFileSQLUser
	extends CFileCache
{

	/**	ルート フォルダ。 */
	private static $rootdir = NUE_ROOT . '/sql/user';

	/**	テーブル定義SQL用クラス オブジェクト。 */
	private static $ddl = null;

	/**	該当実体IDレコード削除SQL用クラス オブジェクト。 */
	private static $deleteFromEntityID = null;

	/**	該当インデックスID削除SQL用クラス オブジェクト。 */
	private static $deleteFromID = null;

	/**	テーブル検索SQL用クラス オブジェクト。 */
	private static $isTableExists = null;

	/**	レコード追加SQL用クラス オブジェクト。 */
	private static $insert = null;

	/**	該当インデックスID検索SQL用クラス オブジェクト。 */
	private static $selectFromId = null;

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
	 *	該当実体IDレコード削除SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getDeleteFromEntityID()
	{
		if(self::$deleteFromEntityID == null)
		{
			self::$deleteFromEntityID = new CFileEntity(self::$rootdir . '/deleteFromEntityID.sql');
		}
		return self::$deleteFromEntityID;
	}

	/**
	 *	該当インデックスID削除SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getDeleteFromID()
	{
		if(self::$deleteFromID == null)
		{
			self::$deleteFromID = new CFileEntity(self::$rootdir . '/deleteFromID.sql');
		}
		return self::$deleteFromID;
	}

	/**
	 *	テーブル検索SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getIsTableExists()
	{
		if(self::$isTableExists == null)
		{
			self::$isTableExists = new CFileEntity(self::$rootdir . '/isTableExists.sql');
		}
		return self::$isTableExists;
	}

	/**
	 *	レコード追加SQL用オブジェクトを取得します。
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
	 *	該当インデックスID検索SQL用オブジェクトを取得します。
	 *
	 *	@return CFileCache ファイルキャッシュ オブジェクト。
	 */
	public static function getSelectFromId()
	{
		if(self::$selectFromId == null)
		{
			self::$selectFromId = new CFileEntity(self::$rootdir . '/selectFromId.sql');
		}
		return self::$selectFromId;
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
