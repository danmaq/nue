<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/dao/CTopic.php');
require_once(NUE_LIB_ROOT . '/dao/CTagTree.php');
require_once(NUE_LIB_ROOT . '/util/CPager.php');

// TODO : これそろそろ分割考えたほうがいいんじゃねえの？

/**
 *	ドキュメントを生成するクラス。
 */
class CDocumentBuilder
{

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

	/**	ここに列挙されたHLML属性は全て無条件で許可されます。 */
	private static $hlmlAutoAllow = array('title');

	/**	DOMオブジェクト。 */
	private $dom;

	/**	XMLルート要素。 */
	private $body;

	/**	XMLルートのタイトル属性。 */
	private $title;

	/**
	 *	ルート要素を作成します。
	 *
	 *	@param DOMDocument $dom DOMオブジェクト。
	 *	@return DOMElement ルート要素。
	 */
	public static function createBody(DOMDocument $dom)
	{
		$result = $dom->createElement('body');
		$result->setAttributeNS(self::URI_XMLNS , 'xmlns:' . self::NS_XHTML, self::URI_XHTML);
		$result->setAttributeNS(self::URI_XMLNS , 'xmlns:' . self::NS_XSI, self::URI_XSI);
		$dom->appendChild($result);
		return $result;
	}

	/**
	 *	XSLスキンへのパスを取得します。
	 *
	 *	@param string $xslpath XSLファイルへのパス。
	 *	@return string XSLファイルへのパス。
	 */
	private static function getSkinPath($xslpath)
	{
		$result = sprintf('skin/%s/%s', $_GET['skin'], $xslpath);
		if(!file_exists(sprintf('%s/%s', NUE_ROOT, $result)))
		{
			$_GET['skin'] = CConfigure::SKINSET;
			// 既定も存在しない場合無限ループになるので再帰にはしない。
			$result = sprintf('skin/%s/%s', $_GET['skin'], $xslpath);
		}
		return $result;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $title タイトル メッセージ。
	 */
	public function __construct($title = '')
	{
		$dom = new DOMDocument('1.0', 'UTF-8');
		$body = self::createBody($dom);
		$this->dom = $dom;
		$title = $this->createAttribute($body, 'title', $title);
		$this->createAttribute($body, 'site', CConfigure::SITE_NAME);
		$this->createAttribute($body, 'ver', CConstants::VERSION);
		$this->createAttribute($body, 'ua', $_SERVER['HTTP_USER_AGENT']);
		$body->setAttributeNS(self::URI_XSI, self::NS_XSI . ':noNamespaceSchemaLocation', 'skin/nue.xsd');
		$this->body = $body;
		$this->title = $title;
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
		if(isset($_GET['err']))
		{
			$topic = $this->createTopic(_('エラー'));
			$p = $this->createParagraph($topic);
			$this->addText($p, $_GET['err']);
		}
		if(strlen(self::$trace) > 0)
		{
			$this->createCodeParagraph($this->createTopic(_('デバッグ用メッセージ')), self::$trace);
		}
		ob_start("ob_gzhandler");
		if(CConfigure::USE_CLIENT_XSLT)
		{
			header('Content-Type: text/xml; charset=UTF-8');
			$dom = $this->getDOM();
			$xsl = $dom->createProcessingInstruction('xml-stylesheet',
				sprintf('type="text/xsl" href="%s"', self::getSkinPath($xslpath)));
			$dom->insertBefore($xsl, $dom->firstChild);
			echo $dom->saveXML();
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
		$xslt = new XSLTProcessor();
		$xsl = new DOMDocument();
		$xsl->load(sprintf('%s/%s', NUE_ROOT, self::getSkinPath($xslpath)));
		$xslt->importStyleSheet($xsl);
		return $xslt->transformToXML($this->getDOM());
	}

	/**
	 *	カテゴリリストを作成します。
	 *
	 *	@return DOMElement カテゴリ一覧情報 オブジェクト。
	 */
	public function createCategoryList()
	{
		$dom = $this->getDOM();
		$result = $dom->createElement('category');
		$tree = new CTagTree();
		$this->getRootElement()->appendChild($result);
		$this->createCategoryListChild($result, $tree->getTree());
		$this->getRootElement()->appendChild($result);
		return $result;
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
			$body =& $user->storage();
			if($enableLogoff)
			{
				$this->createAttribute($result, 'id', $user->getID());
			}
			$this->createAttribute($result, 'name', $body['name']);
			if($body['root'])
			{
				$this->createAttribute($result, 'root', 'root');
			}
		}
		$this->getRootElement()->appendChild($result);
		return $result;
	}

	/**
	 *	検索ワード情報を作成します。
	 *
	 *	@param string $expr 検索ワード。
	 *	@return DOMElement ユーザ情報 オブジェクト。
	 */
	public function createSearchInfo($expr = null)
	{
		$result = $this->getDOM()->createElement('search');
		if($expr !== null && strlen($expr) > 0)
		{
			$this->createAttribute($result, 'tag', $expr);
		}
		$this->getRootElement()->appendChild($result);
		return $result;
	}

	/**
	 *	ページャ情報を作成します。
	 *
	 *	@param CPager $pager ページャ オブジェクト。
	 *	@return DOMElement ユーザ情報 オブジェクト。
	 */
	public function createPagerInfo(CPager $pager = null)
	{
		if($pager === null)
		{
			$pager = new CPager();
		}
		$result = $this->getDOM()->createElement('pager');
		$this->createAttribute($result, 'page', $pager->target);
		$this->createAttribute($result, 'tpp', $pager->TopicsPerPage);
		$this->createAttribute($result, 'max', $pager->maxPage);
		$this->createAttribute($result, 'topics', $pager->topics);
		$this->getRootElement()->appendChild($result);
		return $result;
	}

	/**
	 *	空のトピックを作成します。
	 *
	 *	@param mixed $caption 見出し、または記事DAOオブジェクト。
	 *	@return DOMElement 空のトピック オブジェクト。
	 */
	public function createTopic($caption)
	{
		$dom = $this->getDOM();
		$topic = $dom->createElement('topic');
		$title = $caption;
		if($caption instanceof CTopic)
		{
			$entity = $caption->getEntity();
			$body =& $entity->storage();
			$title = $body['caption'];
			$this->createAttribute($topic, 'id', $caption->getID());
			$this->createAttribute($topic, 'updated', date('Y/n/j', $entity->getUpdated()));
			$this->createAttribute($topic, 'created', date('Y/n/j', $caption->userTimeStamp));
			$ul = null;
			foreach($caption->getDescription() as $desc)
			{
				// TODO : この辺のロジック美しくないなぁ
				if(preg_match('/^@/', $desc))
				{
					$prefix = 1;
					$ol = preg_match('/^@@@/', $desc);
					$separate = preg_match('/^@@/', $desc);
					if($separate)
					{
						$prefix = 2;
						$ul = null;
					}
					if($ol)
					{
						$prefix = 3;
					}
					if($ul === null)
					{
						$ul = $dom->createElement($ol ? 'ol' : 'ul');
						$topic->appendChild($ul);
					}
					$li = $dom->createElement('li');
					$ul->appendChild($li);
					$this->addHLML($li, substr($desc, $prefix), $ul);
				}
				else
				{
					$ul = null;
					$p = $this->createParagraph($topic);
					$this->addHLML($p, $desc);
				}
			}
		}
		$this->createAttribute($topic, 'title', $title);
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
		$result = null;
		if(strlen($text) > 0)
		{
			$result = $this->getDOM()->createDocumentFragment();
			$result->appendXML($text);
			$element->appendChild($result);
		}
		return $result;
	}

	/**
	 *	HTML風言語をパースします。
	 *
	 *	@param DOMNode $element 所属させる要素。
	 *	@param string $expr 文字列。
	 *	@param string $paragraph 一番親となる要素。
	 */
	public function addHLML(DOMNode $element, $expr, DOMNode $paragraph = null)
	{
		$result = array();
		if(preg_match('/\[((?>[^[\]]+)|(?R))*\]/', $expr, $match, PREG_OFFSET_CAPTURE))
		{
			$this->addText($element, substr($expr, 0, $match[0][1]));
			if(preg_match('/\[\[\[(.*?):(.*?)\](.*)\]\]/', $match[0][0], $elm))
			{
				$tag = $elm[1];
				$inner = $elm[3];
				$result = $element;
				$target = $this->getHLMLPath($tag);
				if(file_exists($target))
				{
					$elm[2] = preg_replace('/\\\,/', "\x00", $elm[2]);
					$attrs_base = preg_split('/\,/', $elm[2], -1, PREG_SPLIT_NO_EMPTY);
					$attrs = array();
					for($i = count($attrs_base); --$i >= 0; )
					{
						$kv = preg_split('/=/', preg_replace('/\x00/', ',', $attrs_base[$i]), 2);
						$attrs[$kv[0]] = count($kv) == 2 ? $kv[1] : null;
					}
					if($paragraph === null)
					{
						$paragraph = $element;
					}
					$dom = $this->getDOM();
					require($target);
					if($result !== $element)
					{
						$element->appendChild($result);
					}
				}
				$this->addHLML($result, $inner, $paragraph);
			}
			$this->addHLML($element, substr($expr, $match[0][1] + strlen($match[0][0])), $paragraph);
		}
		else
		{
			$this->addText($element, $expr);
		}
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
	 *	隠し入力を作成します。
	 *
	 *	@param DOMNode $parent 所属させるノード。
	 *	@param string $id キーとなる文字列。
	 *	@param string $value 既定値となる文字列。
	 *	@return DOMElement input要素オブジェクト。
	 */
	public function createHiddenInput(
		DOMNode $parent, $id, $value)
	{
		return $this->createHTMLElement($parent, 'input', array(
			'type' => 'hidden',
			'id' => $id, 'name' => $id,
			'value' => $value));
	}

	/**
	 *	送信ボタンを作成します。
	 *
	 *	@param DOMNode $parent 所属させるノード。
	 *	@param string $caption ボタン名文字列。
	 *	@return DOMElement input要素オブジェクト。
	 */
	public function createSubmitInput(
		DOMNode $parent, $caption)
	{
		return $this->createHTMLElement($parent, 'input', array(
			'type' => 'submit', 'value' => $caption));
	}

	/**
	 *	チェック ボックスを作成します。
	 *
	 *	@param DOMNode $parent 所属させるノード。
	 *	@param string $id キーとなる文字列。
	 *	@param boolean $value 有効かどうか。
	 *	@param string $label ラベル。
	 *	@return DOMElement input要素オブジェクト。
	 */
	public function createCheckInput(
		DOMNode $parent, $id, $value, $label)
	{
		$this->createHTMLElement($parent, 'label', array('for' => $id),
			$label);
		$result = $this->createHTMLElement($parent, 'input', array(
			'type' => 'checkbox',
			'id' => $id, 'name' => $id,
			'value' => 1));
		if($value)
		{
			$this->createAttribute($result, 'checked', 'checked');
		}
		$this->createHTMLElement($parent, 'br');
		return $result;
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
	 *	@param mixed $ascii ASCII入力のみ受け付けるかどうか。
	 *	@param boolean $enabled 有効なフィールドかどうか。
	 *	@return DOMElement input要素オブジェクト。
	 */
	public function createTextInput(
		DOMNode $parent, $type, $id, $value, $label, $min = 0, $max = 255,
		$ascii = false, $enabled = true)
	{
		$this->createHTMLElement($parent, 'label', array('for' => $id),
			$label);
		$regex = '.';
		if($ascii === true)
		{
			$regex = '[0-9A-Za-z]';
		}
		elseif($ascii === 0)
		{
			$regex = '[0-9]';
		}
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
			'id' => $id, 'name' => $id, 'placeholder' => $label,
			'maxlength' => CDataEntity::SIZE, 'required' => 'required',
			'cols' => 40, 'rows' => 5));
		$this->addText($result, $value);
		$this->createHTMLElement($parent, 'br');
		return $result;
	}

	/**
	 *	HLMLプラグインのパスを取得します。
	 *
	 *	@param string $tag クエリ文字列。
	 *	@return string パス。
	 */
	private function getHLMLPath($tag)
	{
		return NUE_ROOT . preg_replace('/(\.|\/){2,}/', '\1', sprintf('/hlml/%s.php', $tag));
	}

	/**
	 *	既定のHLML変換をします。
	 *
	 *	@param string $tag 要素名。
	 *	@param array $attrs 属性一覧が格納された連想配列。
	 *	@param array $allow 許容する要素一覧。(全許容する場合null、全禁止する場合空の配列)
	 *	@return DOMElement 要素オブジェクト。
	 */
	private function simpleHMLMConvert($tag, array $attrs, array $allow = null)
	{
		$result = $this->getDOM()->createElementNS(
			self::URI_XHTML, sprintf('%s:%s', self::NS_XHTML, $tag));
		$allAllow = $allow === null;
		if(!$allAllow)
		{
			$allow = array_merge($allow, self::$hlmlAutoAllow);
		}
		foreach(array_keys($attrs) as $item)
		{
			if($allAllow || in_array($item, $allow))
			{
				$this->createAttribute($result, $item, $attrs[$item]);
			}
		}
		return $result;
	}

	/**
	 *	カテゴリリストを作成します。
	 *
	 *	@param DOMNode $topic 所属させるノード。
	 *	@param array $tree カテゴリ ツリー情報。
	 */
	private function createCategoryListChild(DOMNode $parent, array $tree)
	{
		$dom = $this->getDOM();
		$keys = array_keys($tree);
		if(count($keys) > 0)
		{
			$len = max($keys);
			for($i = 0; $i <= $len; $i++)
			{
				$li = $dom->createElement('li');
				$parent->appendChild($li);
				$item = $tree[$i];
				if(isset($tree[$item]))
				{
					$ul = $dom->createElement('ul');
					$lh = $dom->createElement('lh');
					$this->createAttribute($lh, 'href', urlencode($item));
					$this->addText($lh, $item);
					$li->appendChild($ul);
					$ul->appendChild($lh);
					$this->createCategoryListChild($ul, $tree[$item]);
				}
				else
				{
					$this->createAttribute($li, 'href', urlencode($item));
					$this->addText($li, $item);
				}
			}
		}
	}
}

/**
 *	ログを追加します。
 *
 *	@param string $body ログ。
 */
function trace($body)
{
	CDocumentBuilder::$trace .= "\n\n" . $body;
}

?>
