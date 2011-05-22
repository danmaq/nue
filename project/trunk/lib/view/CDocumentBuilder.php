<?php

require_once(dirname(__FILE__) . '/../CConstants.php');

/**
 *	ドキュメントを生成するクラス。
 */
class CDocumentBuilder
{

	/**	XML名前空間URL。 */
	const URI_XMLNS = 'http://www.w3.org/2000/xmlns/';

	/**	XHTML名前空間URL。 */
	const URI_XHTML = 'http://www.w3.org/1999/xhtml';

	/**	XHTML名前空間。 */
	const NS_XHTML = 'xhtml';

	/**	DOMオブジェクト。 */
	private $dom;

	/**	XMLルート要素。 */
	private $body;

	/**	XMLルートのタイトル属性。 */
	private $title;

	/**
	 *	コンストラクタ。
	 *
	 *	param string $title タイトル メッセージ。
	 */
	public function __construct($title = '')
	{
		$dom = new DOMDocument('1.0', 'UTF-8');
		$this->dom = $dom;
		$body = $dom->createElement('body');
		$title = $this->createCaption($title);
		$this->body = $body;
		$this->title = $title;
		$dom->appendChild($body);
		$body->appendChild($title);
		$siteName = $dom->createAttribute('site');
		$siteName->value = CConfigure::SITE_NAME;
		$body->appendChild($siteName);
		$body->setAttributeNS(self::URI_XMLNS , 'xmlns:' . self::NS_XHTML, self::URI_XHTML);
	}

	/**
	 *	DOMオブジェクトを取得します。
	 *
	 *	@return DOMDocument DOMオブジェクト。
	 */
	public function getDOM()
	{
		return $this->dom;
	}

	/**
	 *	ルート要素を取得します。
	 *
	 *	@return DOMElement ルート要素。
	 */
	public function getRootElement()
	{
		return $this->body;
	}

	/**
	 *	タイトルを取得します。
	 *
	 *	@return string タイトル。
	 */
	public function getTitle()
	{
		return $this->title->value;
	}

	/**
	 *	タイトルを設定します。
	 *
	 *	@param string $value タイトル。
	 */
	public function setTitle($value)
	{
		$this->title->value = $value;
	}

	/**
	 *	XSLTを介してHTMLを生成し、出力します。
	 *
	 *	@param string $xslpath XSLファイルへのパス。
	 *	@return string 出力されたHTML文字列。
	 */
	public function output($xslpath)
	{
		$xhtml = 'application/xhtml+xml';
		$accept = isset($_SERVER{'HTTP_ACCEPT'}) ? $_SERVER{'HTTP_ACCEPT'} : $xhtml;
		$pattern = sprintf('/%s/', preg_quote($xhtml, '/'));
		ob_start("ob_gzhandler");
		header(sprintf('Content-Type: %s; charset=UTF-8',
			preg_match($pattern, $accept) ? $xhtml : 'text/html'));
		echo $this->createHTML($xslpath);
	}

	/**
	 *	XSLTを介してHTMLを生成します。
	 *
	 *	@param string $xslpath XSLファイルへのパス。
	 *	@return string HTML文字列。
	 */
	public function createHTML($xslpath)
	{
		$xslt = new XSLTProcessor();
		$xsl = new DOMDocument();
		$xsl->load(sprintf('%s/skin/%s/%s', CConstants::$ROOT_DIR, CConfigure::SKINSET, $xslpath));
		$xslt->importStyleSheet($xsl);
		return $xslt->transformToXML($this->getDOM());
	}

	/**
	 *	空のトピックを作成します。
	 *
	 *	@param string $caption 見出し。
	 *	@return DOMElement 空のトピック オブジェクト。
	 */
	public function createTopic($caption)
	{
		$topic = $this->getDOM()->createElement('topic');
		$topic->appendChild($this->createCaption($caption));
		$this->getRootElement()->appendChild($topic);
		return $topic;
	}

	/**
	 *	空の段落を作成します。
	 *
	 *	@param string $topic 所属させるトピック。
	 *	@param string $caption 小見出し。省略時は作成されません。
	 *	@return DOMElement 空の段落 オブジェクト。
	 */
	public function createParagraph(DOMNode $topic, $caption = null)
	{
		$paragraph = $this->getDOM()->createElement('p');
		if($caption !== null)
		{
			$topic->appendChild($this->createCaption($caption));
		}
		$topic->appendChild($paragraph);
		return $paragraph;
	}

	/**
	 *	シンプルなメッセージを生成します。
	 *
	 *	@param string $caption 見出し。
	 *	@param string $description 本文。
	 *	@return DOMDocument DOMオブジェクト。
	 */
	public function createSimpleMessage($caption, $description)
	{
		if(!($this->getTitle()))
		{
			$this->setTitle($caption);
		}
		$paragraph = $this->createParagraph($this->createTopic($caption));
		$dom = $this->getDOM();
		$paragraph->appendChild($dom->createTextNode($description));
		return $dom;
	}

	/**
	 *	見出し属性を作成します。
	 *
	 *	このメソッドで作成された属性はどこにも所属していませんので
	 *	手動でappendChildする必要があります。
	 *
	 *	@param string $caption 見出し。
	 *	@preturn 見出し属性オブジェクト。
	 */
	private function createCaption($caption)
	{
		$title = $this->getDOM()->createAttribute('title');
		$title->value = $caption;
		return $title;
	}
}

?>
