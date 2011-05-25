<?php

/**
 *	ファイル キャッシュ クラス。
 */
abstract class CFileCache
{

	/**	ファイル パス。 */
	private $fname;

	/**	ファイルの中身。 */
	private $body;

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $fname ファイル パス。
	 */
	private function __construct($fname)
	{
		$this->fname = $fname;
		$body = '';
		if(file_exists($fname))
		{
			$body = implode('', file($fname));
		}
		$this->body = $body;
	}

	/**
	 *	ファイル パスを取得します。
	 *
	 *	@return string ファイル パス。
	 */
	public function getFilePath()
	{
		return $this->fname;
	}

	/**
	 *	内容を取得します。
	 *
	 *	@return string 内容文字列。
	 */
	public function getBody()
	{
		return $this->body;
	}

}

?>
