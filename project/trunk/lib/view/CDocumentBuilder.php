<?php

require_once(dirname(__FILE__) . '/../CConstants.php');

/**
 *	�h�L�������g�𐶐�����N���X�B
 */
class CDocumentBuilder
{

	/**	XML���O���URL�B */
	const URI_XMLNS = 'http://www.w3.org/2000/xmlns/';

	/**	XHTML���O���URL�B */
	const URI_XHTML = 'http://www.w3.org/1999/xhtml';

	/**	XHTML���O��ԁB */
	const NS_XHTML = 'xhtml';

	/**	DOM�I�u�W�F�N�g�B */
	private $dom;

	/**	XML���[�g�v�f�B */
	private $body;

	/**	XML���[�g�̃^�C�g�������B */
	private $title;

	/**
	 *	�R���X�g���N�^�B
	 *
	 *	param string $title �^�C�g�� ���b�Z�[�W�B
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
	 *	DOM�I�u�W�F�N�g���擾���܂��B
	 *
	 *	@return DOMDocument DOM�I�u�W�F�N�g�B
	 */
	public function getDOM()
	{
		return $this->dom;
	}

	/**
	 *	���[�g�v�f���擾���܂��B
	 *
	 *	@return DOMElement ���[�g�v�f�B
	 */
	public function getRootElement()
	{
		return $this->body;
	}

	/**
	 *	�^�C�g�����擾���܂��B
	 *
	 *	@return string �^�C�g���B
	 */
	public function getTitle()
	{
		return $this->title->value;
	}

	/**
	 *	�^�C�g����ݒ肵�܂��B
	 *
	 *	@param string $value �^�C�g���B
	 */
	public function setTitle($value)
	{
		$this->title->value = $value;
	}

	/**
	 *	XSLT�����HTML�𐶐����A�o�͂��܂��B
	 *
	 *	@param string $xslpath XSL�t�@�C���ւ̃p�X�B
	 *	@return string �o�͂��ꂽHTML������B
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
	 *	XSLT�����HTML�𐶐����܂��B
	 *
	 *	@param string $xslpath XSL�t�@�C���ւ̃p�X�B
	 *	@return string HTML������B
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
	 *	��̃g�s�b�N���쐬���܂��B
	 *
	 *	@param string $caption ���o���B
	 *	@return DOMElement ��̃g�s�b�N �I�u�W�F�N�g�B
	 */
	public function createTopic($caption)
	{
		$topic = $this->getDOM()->createElement('topic');
		$topic->appendChild($this->createCaption($caption));
		$this->getRootElement()->appendChild($topic);
		return $topic;
	}

	/**
	 *	��̒i�����쐬���܂��B
	 *
	 *	@param string $topic ����������g�s�b�N�B
	 *	@param string $caption �����o���B�ȗ����͍쐬����܂���B
	 *	@return DOMElement ��̒i�� �I�u�W�F�N�g�B
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
	 *	�V���v���ȃ��b�Z�[�W�𐶐����܂��B
	 *
	 *	@param string $caption ���o���B
	 *	@param string $description �{���B
	 *	@return DOMDocument DOM�I�u�W�F�N�g�B
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
	 *	���o���������쐬���܂��B
	 *
	 *	���̃��\�b�h�ō쐬���ꂽ�����͂ǂ��ɂ��������Ă��܂���̂�
	 *	�蓮��appendChild����K�v������܂��B
	 *
	 *	@param string $caption ���o���B
	 *	@preturn ���o�������I�u�W�F�N�g�B
	 */
	private function createCaption($caption)
	{
		$title = $this->getDOM()->createAttribute('title');
		$title->value = $caption;
		return $title;
	}
}

?>
