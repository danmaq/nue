<?php

/**
 *	PHPエラーを例外(Exception)へ変換
 */
class CErrorException
	extends Exception
{

	/**
	 *	コンストラクタ。
	 *
	 *	@param int $errno エラー番号。
	 *	@param string $errstr 
	 *	@param string $errfile 
	 *	@param int $errline 
	 */
	public function __construct($errno, $errstr, $errfile, $errline)
	{
		// エラー番号とエラーレベルのマッピング
		$errlev = array(
			E_USER_ERROR   => 'FATAL',
			E_ERROR        => 'FATAL',
			E_USER_WARNING => 'WARNING',
			E_WARNING      => 'WARNING',
			E_USER_NOTICE  => 'NOTICE',
			E_NOTICE       => 'NOTICE',
			E_STRICT       => 'E_STRICT'
		);
	
		$add_msg= (string)$errno;
		if (isset($errlev[$errno])) {
			$add_msg = $errlev[$errno] . ' : ';
		}
		parent::__construct($add_msg . $errstr, $errno);
		$this->file = $errfile;
		$this->line = $errline;
	}
}

/**
 *	エラーが発生した場合に呼び出される処理。
 *
 *	@param int $errno エラー番号。
 *	@param string $errstr 
 *	@param string $errfile 
 *	@param int $errline 
 */
function errorHandler($errno, $errstr, $errfile, $errline)
{
	throw new CErrorException($errno, $errstr, $errfile, $errline);
}
set_error_handler('errorHandler');

?>
