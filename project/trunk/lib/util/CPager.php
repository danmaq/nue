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

	/**	現在の件数。 */
	public $topics = 0;

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
	 *	@return array リミット情報。['start']:開始レコード、['length']:レコード数。
	 */
	public function getLimit()
	{
		$tpp = $this->TopicsPerPage;
		return array(
			'start' => array($this->target * $tpp, PDO::PARAM_INT),
			'length' => array($tpp, PDO::PARAM_INT));
	}

	/**
	 *	件数から最大ページ番号を設定し、返します。
	 *
	 *	@param int $topics 件数。
	 *	@return int 最大ページ番号。
	 */
	public function setMaxPagesFromCount($topics)
	{
		$result = ceil($topics / $this->TopicsPerPage);
		$this->topics = $topics;
		$this->maxPage = $result;
		return $result;
	}
}

?>
