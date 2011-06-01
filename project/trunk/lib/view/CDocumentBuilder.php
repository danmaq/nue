<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');

/**
 *	ドキュメントを生成するクラス。
 */
class CDocumentBuilder
{

	/**	生のXMLを出力するかどうか。 */
	const DEBUG_OUTPUT_RAW_XML = false;

	/**	XML名前空間URL。 */
	const URI_XMLNS = 'http://www.w3.org/2000/xmlns/';

	/**	XHTML名前空間URL。 */
	const URI_XHTML = 'http://www.w3.org/1999/xhtml';

	/**	XML Schema名前空間URL。 */
	const URI_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

	/**	XHTML名前空間。 */
	const NS_XHTML = 'xhtml';

	/**	XHTML名前空間。 */
	const NS_XSI = 'xsi';

	/**	トレース メッセージ。 */
	public static $trace = '';

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
		$title = $this->createAttribute($body, 'title', $title);
		$this->createAttribute($body, 'site', CConfigure::SITE_NAME);
		$this->createAttribute($body, 'ver', CConstants::VERSION);
		$this->createAttribute($body, 'ua', $_SERVER['HTTP_USER_AGENT']);
		$this->body = $body;
		$this->title = $title;
		$dom->appendChild($body);
		$body->setAttributeNS(self::URI_XMLNS , 'xmlns:' . self::NS_XHTML, self::URI_XHTML);
		$body->setAttributeNS(self::URI_XMLNS , 'xmlns:' . self::NS_XSI, self::URI_XSI);
		$body->setAttributeNS(self::URI_XSI, self::NS_XSI . ':noNamespaceSchemaLocation', './skin/nue.xsd');
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
		ob_start("ob_gzhandler");
		if(self::DEBUG_OUTPUT_RAW_XML)
		{
			header('Content-Type: text/xml; charset=UTF-8');
			echo $this->getDOM()->saveXML();
		}
		else
		{
			$xhtml = 'application/xhtml+xml';
			$accept = isset($_SERVER{'HTTP_ACCEPT'}) ? $_SERVER{'HTTP_ACCEPT'} : $xhtml;
			$pattern = sprintf('/%s/', preg_quote($xhtml, '/'));
			header(sprintf('Content-Type: %s; charset=UTF-8',
				preg_match($pattern, $accept) ? $xhtml : 'text/html'));
			header('X-UA-Compatible : IE=edge');
			echo $this->createHTML($xslpath);
		}
	}

	/**
	 *	XSLTを介してHTMLを生成します。
	 *
	 *	@param string $xslpath XSLファイルへのパス。
	 *	@return string HTML文字列。
	 */
	public function createHTML($xslpath)
	{
		if(self::$trace != null)
		{
			$this->createCodeParagraph($this->createTopic(_('デバッグ用メッセージ')), self::$trace);
		}
		
		$xslt = new XSLTProcessor();
		$xsl = new DOMDocument();
		$xsl->load(sprintf('%s/skin/%s/%s', NUE_ROOT, CConfigure::SKINSET, $xslpath));
		$xslt->importStyleSheet($xsl);
		return $xslt->transformToXML($this->getDOM());
	}

	/**
	 *	ユーザ情報を作成します。
	 *
	 *	@param CUser $user ユーザDAOオブジェクト。
	 *	@param boolean $enableLogoff ログオフ可能かどうか。
	 *	@return DOMElement ユーザ情報 オブジェクト。
	 */
	public function createUserLogonInfo(CUser $user = null, $enableLogoff = true)
	{
		$result = $this->getDOM()->createElement('user');
		if($user !== null)
		{
			$body =& $user->getEntity()->storage();
			$result = $this->getDOM()->createElement('user');
			if($enableLogoff)
			{
				$this->createAttribute($result, 'id', $user->getID());
			}
			$this->createAttribute($result, 'name', $body['name']);
		}
		$this->getRootElement()->appendChild($result);
		return $result;
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
		$this->createAttribute($topic, 'title', $caption);
		$this->getRootElement()->appendChild($topic);
		return $topic;
	}

	/**
	 *	空のフォームを作成します。
	 *
	 *	@param DOMNode $topic 所属させるトピック。
	 *	@param string $action 接続先のURL。
	 *	@param string $method 接続するメソッド。
	 *	@return DOMElement 空のフォーム オブジェクト。
	 */
	public function createForm(DOMNode $topic, $action, $method = 'POST')
	{
		$form = $this->getDOM()->createElement('form');
		$this->createAttribute($form, 'action', $action);
		$this->createAttribute($form, 'method', $method);
		$topic->appendChild($form);
		return $form;
	}

	/**
	 *	空の段落を作成します。
	 *
	 *	@param DOMNode $topic 所属させるトピック。
	 *	@param string $caption 小見出し。省略時は作成されません。
	 *	@return DOMElement 空の段落 オブジェクト。
	 */
	public function createParagraph(DOMNode $topic, $caption = null)
	{
		$paragraph = $this->getDOM()->createElement('p');
		if($caption !== null)
		{
			$this->createAttribute($paragraph, 'title', $caption);
		}
		$topic->appendChild($paragraph);
		return $paragraph;
	}

	/**
	 *	コードなど等幅の段落を作成します。
	 *
	 *	@param DOMNode $topic 所属させるトピック。
	 *	@param DOMNode $body 内容。
	 *	@param string $caption 小見出し。省略時は作成されません。
	 *	@return DOMElement 空の段落 オブジェクト。
	 */
	public function createCodeParagraph(DOMNode $topic, $body, $caption = null)
	{
		$dom = $this->getDOM();
		$paragraph = $this->createParagraph($topic, $caption);
		$code = $this->createHTMLElement($paragraph, 'code');
		foreach(explode("\n", $body) as $item)
		{
			$code->appendChild($dom->createTextNode($item));
			$this->createHTMLElement($code, 'br');
		}
		return $paragraph;
	}

	/**
	 *	シンプルなメッセージを生成します。
	 *
	 *	@param string $caption 見出し。
	 *	@param string $description 本文。
	 *	@param string $seeother 参考資料など。
	 *	@return DOMElement トピック オブジェクト。
	 */
	public function createSimpleMessage($caption, $description, $seeother = null)
	{
		if(!($this->getTitle()))
		{
			$this->setTitle($caption);
		}
		$topic = $this->createTopic($caption);
		$paragraph = $this->createParagraph($topic);
		$dom = $this->getDOM();
		$paragraph->appendChild($dom->createTextNode($description));
		if($seeother !== null)
		{
			$this->createCodeParagraph($topic, $seeother, _('参考'));
		}
		return $topic;
	}

	/**
	 *	HTML要素を作成します。
	 *
	 *	@param DOMNode $element 所属させる要素。
	 *	@param string $name 要素名。
	 *	@param array $attr 属性一覧。
	 *	@param mixed $body 挿入する内容。DOMNodeとstringに対応します。
	 *	@preturn DOMElement 作成された要素オブジェクト。
	 */
	public function createHTMLElement(DOMNode $element, $name, array $attr = array(), $body = null)
	{
		$result = $this->getDOM()->createElementNS(
			self::URI_XHTML, sprintf('%s:%s', self::NS_XHTML, $name));
		$element->appendChild($result);
		foreach (array_keys($attr) as $item)
		{
			$this->createAttribute($result, $item, $attr[$item]);
		}
		if($body != null)
		{
			if($body instanceof DOMNode)
			{
				$result->appendChild($body);
			}
			else
			{
				$this->addText($result, $body);
			}
		}
		return $result;
	}

	/**
	 *	テキストを挿入します。
	 *
	 *	@param DOMNode $element 所属させる要素。
	 *	@param string $text テキスト。
	 *	@preturn DOMNode 作成されたテキスト ノード オブジェクト。
	 */
	public function addText(DOMNode $element, $text)
	{
		$result = $this->getDOM()->createTextNode($text);
		$element->appendChild($result);
		return $result;
	}

	/**
	 *	属性を作成します。
	 *
	 *	@param DOMNode $element 所属させる要素。
	 *	@param string $name 属性。
	 *	@param string $value 値。
	 *	@preturn DOMNode 作成された属性オブジェクト。
	 */
	public function createAttribute(DOMNode $element, $name, $value)
	{
		$attr = $this->getDOM()->createAttribute($name);
		$attr->value = $value;
		$element->appendChild($attr);
		return $attr;
	}

	/**
	 *	1行入力ボックスを作成します。
	 *
	 *	@param DOMNode $parent 所属させるノード。
	 *	@param string $type 入力タイプ。
	 *	@param string $id キーとなる文字列。
	 *	@param string $value 既定値となる文字列。
	 *	@param string $label ラベル。
	 *	@param integer $min 最小文字数。
	 *	@param integer $max 最大文字数。
	 *	@param boolean $ascii ASCII入力のみ受け付けるかどうか。
	 *	@param boolean $enabled 有効なフィールドかどうか。
	 *	@return DOMElement input要素オブジェクト。
	 */
	public function createTextInput(
		DOMNode $parent, $type, $id, $value, $label, $min = 0, $max = 255,
		$ascii = true, $enabled = true)
	{
		$this->createHTMLElement($parent, 'label', array('for' => $id),
			$label);
		$result = $this->createHTMLElement($parent, 'input', array(
			'type' => $type,
			'id' => $id, 'name' => $id,
			'value' => $value,
			'maxlength' => $max,
			'pattern' => sprintf('^%s{%d,%d}$', $ascii ? '[0-9A-Za-z]' : '.', $min, $max),
			'placeholder' => sprintf(_('%d～%d文字以内'), $min, $max)));
		if($min > 0)
		{
			$this->createAttribute($result, 'required', 'required');
		}
		if(!$enabled)
		{
			$this->createAttribute($result, 'disabled', 'disabled');
			$this->createAttribute($result, 'readonly', 'readonly');
		}
		$this->createHTMLElement($parent, 'br');
		return $result;
	}

	/**
	 *	複数行入力ボックスを作成します。
	 *
	 *	@param DOMNode $parent 所属させるノード。
	 *	@param string $id キーとなる文字列。
	 *	@param string $label ラベル。
	 *	@param string $value 既定値となる文字列。
	 *	@return DOMElement textarea要素オブジェクト。
	 */
	public function createTextArea(DOMNode $parent, $id, $label, $value = ' ')
	{
		$this->createHTMLElement($parent, 'label', array('for' => $id),
			$label);
		$result = $this->createHTMLElement($parent, 'textarea', array(
			'id' => $id, 'name' => $id, 'placeholder' => $label));
		$this->addText($result, $value);
		$this->createHTMLElement($parent, 'br');
		return $result;
	}
}

/**
 *	ログを追加します。
 *
 *	@param string $body ログ。
 */
function trace($body)
{
	CDocumentBuilder::$trace .= $body;
}

?>
