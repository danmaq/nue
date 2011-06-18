<?php

require_once(NUE_CONSTANTS);

/**
 *	ページャ クラス。
 */
class CPager
{

	/**	最大ページ数。 */
	public $maxPage = 0;

	/**	現在のページ番号。 */
	public $target = 0;

	/**	1ページの最大記事数。 */
	public $TopicsPerPage = CConfigure::DEFAULT_TOPIC_PER_PAGE;

	/**
	 *	コンストラクタ。
	 *
	 *	@param int $target 要求するページ。
	 *	@param int $tpp 1ページの最大記事数。
	 */
	public function __construct($target = 0, $topicPerPage = null)
	{
		$this->target = $target;
		$this->topicPerPage =
			$topicPerPage === null ? CConfigure::DEFAULT_TOPIC_PER_PAGE : $topicPerPage;
	}

	/**
	 *	データベースに渡すリミット情報を取得します。
	 *
	 *	@return array リミット情報。[0]:開始レコード、[1]:レコード数。
	 */
	public function getLimit()
	{
		$tpp = $this->$TopicsPerPage;
		return array($this->target * $tpp, $tpp);
	}
}

?>
