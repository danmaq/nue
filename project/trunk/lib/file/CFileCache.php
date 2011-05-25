<?php

/**
 *	ファイル キャッシュ クラス。
 */
abstract class CFileCache
{

	/**	ファイル パス。 */
	private $filePath;

	/**	ファイルの中身。 */
	private $body;

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $filePath ファイル パス。
	 */
	private function __construct($filePath)
	{
		$this->filePath = $filePath;
		$body = '';
		if(file_exists($filePath))
		{
			$body = implode('', file($filePath));
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
		return $this->filePath;
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
