<?php

/**
 *	テキスト ファイル キャッシュ クラス。
 */
class CFileCache
{

	/**	ファイル パス。 */
	private $filePath;

	/**	ファイルの中身一覧。 */
	private $bodyList;

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $filePath ファイル パス。
	 */
	protected function __construct($filePath)
	{
		$this->filePath = $filePath;
		$this->bodyList = array();
	}

	/**
	 *	ファイル パスを取得します。
	 *
	 *	@return string ファイル パス文字列。
	 */
	public function getPath()
	{
		return $this->filePath;
	}

	/**
	 *	ファイルの内容を取得します。
	 *
	 *	@param string $fileName ファイル名。
	 *	@return string ファイルの内容。
	 */
	public function load($fileName)
	{
		$bodyList = $this->bodyList;
		if(!isset($bodyList[$fileName]))
		{
			$body = file_get_contents(sprintf('%s/%s', $this->getPath(), $fileName));
			if($body === false)	// 0バイトすらも読めなかったら
			{
				throw new Exception(_('ファイルが見つかりません。'));
			}
			$bodyList[$fileName] = $body;
		}
		return $bodyList[$fileName];
	}
}

?>
