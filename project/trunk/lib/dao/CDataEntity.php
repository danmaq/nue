<?php

require_once('IDAO.php');

/*

必要な機能

指定IDをロード
新規作成
削除
コミット

*/

/**
 *	データ実体クラス。
 */
class CDataEntity
	implements IDAO
{

	/**	実体ID(GUID)。 */
	private $id;

	/**	更新日時。 */
	private $updated;

	/**	記憶領域(連想配列)。 */
	private $body;

	/**
	 *	コンストラクタ。
	 */
	public function __construct($id = null)
	{
		
		if($id === null)
		{
			$id = self::createGUID();
		}
		$this->id = $id;
	}

	/**
	 *	GUIDを生成します。
	 *
	 *	@return string GUID文字列。
	 */
	public static function createGUID()
	{
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
			mt_rand(0, 65535), mt_rand(0, 65535),
			mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151),
			mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
 	}

	/**
	 *	更新日時を取得します。
	 *
	 *	@return mixed 更新日時。
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 *	記憶領域を取得します。
	 *
	 *	@return mixed 記憶領域。
	 */
	public function storage()
	{
		return $this->body;
	}

	/**
	 *	実体IDを取得します。
	 *
	 *	@return string 実体ID(GUID)。
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	public function getEntity()
	{
		return $this;
	}

	/**
	 *	コミットします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function commit()
	{
		return false;
	}

	/**
	 *	ロールバックします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function rollback()
	{
		return false;
	}
}

?>
