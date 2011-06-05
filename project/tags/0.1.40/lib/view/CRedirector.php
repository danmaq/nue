<?php

require_once(NUE_CONSTANTS);

/**
 *	リダイレクトを出力するクラス。
 */
class CRedirector
{

	/**
	 *	リダイレクトします。
	 *
	 *	@param mixed $params クエリ情報。連想配列または文字列を指定します。
	 */
	public static function seeOther($params = '')
	{
		if(preg_match('/HTTP\/1\.1/', $_SERVER['SERVER_PROTOCOL']))
		{
			header('HTTP/1.1 303 See Other');
		}
		else
		{
			header('HTTP/1.0 302 Moved Temporarily');
		}
		header('Location: ' . self::path_to_url() . self::createQuery($params));
	}

	/**
	 *	クエリ文字列を作成します。
	 *
	 *	@param mixed $params クエリ情報。連想配列または文字列を指定します。
	 *	@return string クエリ文字列。
	 */
	private static function createQuery($params = '')
	{
		$result = '';
		if(is_array($params))
		{
			$params = http_build_query($params);
		}
		if(is_string($params) && strlen($params) > 0)
		{
			$result .= '?' . $params;
		}
		return $result;
	}

	/**
	 * パスから絶対URLを作成します。
	 *
	 * @param string $path パス
	 * @param int $default_port デフォルトのポート（そのポートである場合にはURLに含めない）
	 * @return string URL
	 */
	private static function path_to_url($path = NUE_ROOT, $default_port = 80)
	{
		//ドキュメントルートのパスとURLの作成
		$document_root_url = $_SERVER['SCRIPT_NAME'];
		$document_root_path = $_SERVER['SCRIPT_FILENAME'];
		while(basename($document_root_url) === basename($document_root_path))
		{
			$document_root_url = dirname($document_root_url);
			$document_root_path = dirname($document_root_path);
		}
		if($document_root_path === '/')
		{
			$document_root_path = '';
		}
		if($document_root_url === '/')
		{
			$document_root_url = '';
		}

		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$port = ($_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != $default_port) ?
			':' . $_SERVER['SERVER_PORT'] : '';
		$document_root_url =
			$protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $document_root_url;

		// 絶対パスの取得 (realpath関数ではファイルが存在しない場合や、
		// シンボリックリンクである場合にうまくいかない)
		$absolute_path = realpath($path);
		$result = false;
		if($absolute_path)
		{
			if(substr($absolute_path, -1) !== '/' && substr($path, -1) === '/')
			{
			    $absolute_path .= '/';
			}

			//パスを置換して返す
			$uri = str_replace($document_root_path, $document_root_url, $absolute_path);
			if($absolute_path !== $uri)
			{
			    $result = $uri;
			}
		}
		return $result;
	}

}

?>
