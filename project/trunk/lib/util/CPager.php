<?php

/**
 *	ページャ クラス。
 */
class CPager
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	現在のページ番号。 */
	private $now;

	/**	1ページの最大記事数。 */
	private $TopicsPerPage;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CPager();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
	}

	/**
	 *	要求する情報を設定します。
	 *
	 *	@param int $now 要求するページ。
	 *	@param int $tpp 1ページの最大記事数。
	 */
	public function setInfo($now, $topicPerPage)
	{
		$this->now = $now;
		$this->topicPerPage = $topicPerPage;
	}
}

?>
