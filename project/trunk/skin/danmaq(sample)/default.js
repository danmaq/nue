/**	フェード時間。 */
var FADETIME = 50;

/**	現在検索しているタグ キーワード。 */
var m_current_tag = '';

/**	記事ウィンドウ オブジェクト一覧。 */
var m_sections = Array();

////////////////////////////////////////////////////////////

/**
 *	記事ウィンドウ クラス。
 *
 *	@param section 記事DOMオブジェクト
 */
function CSection(section)
{

	var span2TagTarget = Array('iframe', 'img');
	var jSection = $(section);

	/**	表示するかどうか。 */
	this.visible = true;

	/**	記事内容。 */
	this.article = jSection.find('.article');

	/**	タイトルバー 兼 大見出し。 */
	this.titleBar = jSection.find('h2.title');

	/**
	 *	表示および非表示を切り替えます。
	 *
	 *	@param immediately 即時切り替えるかどうか。省略時は一定時間アニメーションします。
	 */
	this.toggleVisible = function(immediately)
	{
		this.visible = !this.visible;
		var lamp = 'solid';
		var article = this.article;
		var fadeTime = immediately ? undefined : FADETIME;
		if(this.visible)
		{
			article.show(fadeTime);
		}
		else
		{
			lamp = 'double';
			article.hide(fadeTime);
		}
		this.titleBar.css('border-left-style', lamp);
	}

	/**
	 *	特定タグを変換します。
	 */
	this.convertTag = function()
	{
		for(var i = span2TagTarget.length; --i >= 0; )
		{
			this.span2Tag(span2TagTarget[i]);
		}
	}

	/**
	 *	タイトルバーを初期化します。
	 */
	this.initializeTitleBar = function()
	{
		var getFalse = function(){ return false; };
		var instance = this;
		var titleBar = this.titleBar;
		titleBar.click(function(){ instance.toggleVisible(); });
		var titleBarColor = titleBar.css('color');
		titleBar.hover(
			function(){ $(this).css('color', 'White'); },
			function(){ $(this).css('color', titleBarColor); });
		titleBar.mousedown(getFalse);
		titleBar.select(getFalse);
		titleBar.attr('unselectable', 'on');
		titleBar.css('cursor', 'pointer');
		titleBar.css('user-select', 'none');
	}

	/**
	 *	特定タグを変換します。
	 *
	 *	@param name 変換するタグ名。
	 */
	this.span2Tag = function(name)
	{
		var targets = this.article.find('span.' + name);
		for(var j = targets.length; --j >= 0; )
		{
			var target = $(targets[j]);
			var element = $(document.createElement(target.attr('class')));
			var attrs = target.children('*');
			for(var i = attrs.length; --i >= 0; )
			{
				var attr = $(attrs[i]);
				var key = attr.attr('class');
				if(key == '__body__')
				{
					try
					{
						element.prepend(attrs[i]);
					}
					catch(e)
					{
						// TODO : やっぱこれまずいよな……
					}
				}
				else
				{
					element.attr(key, attr.text());
				}
			}
			target.after(element);
			target.remove();
		}
	}

	/** Constructor */
	{
		this.initializeTitleBar();

		// ヘッダ部入力のサイズ制限
		this.article.find('input:text').addClass('text');
		this.article.find('input:password').addClass('text');
	}
}

/**
 *	カテゴリ クラス。
 *
 *	@param {Object} cat カテゴリDOMオブジェクト
 */
function CCategory(cat)
{

	/**	表示するかどうか。 */
	this.visible = true;

	/**	カテゴリ本体。 */
	this.category = $(cat);

	/**	子カテゴリ。 */
	this.child = this.category.children('ul');

	/**	フォルダ。 */
	this.folder = this.category.children('a');

	/**
	 *	表示および非表示を切り替えます。
	 *
	 *	@param immediately 即時切り替えるかどうか。省略時は一定時間アニメーションします。
	 */
	this.toggleVisible = function(immediately)
	{
		this.visible = !this.visible;
		var child = this.child;
		var fadeTime = immediately ? undefined : FADETIME;
		var strOpenFlag = '> ';
		if(this.visible)
		{
			child.show(fadeTime);
		}
		else
		{
			child.hide(fadeTime);
			strOpenFlag = '+ ';
		}

		// TODO : 要素置換よりもテキスト置換の方が良くね？
		var folder = this.folder;
		folder.find('span').remove();
		var openFlag = $(document.createElement('span'));
		openFlag.append(strOpenFlag);
		folder.prepend(openFlag);
	}

	/**
	 *	リンクをフォルダとして初期化します。
	 */
	this.initializeFolder = function()
	{
		var folder = this.folder;
		var folderIndex = folder.clone();
		folderIndex.prepend('index:');
		this.child.prepend(folderIndex);
		folder.addClass('folder');
		var instance = this;
		folder.click(
			function()
			{
				instance.toggleVisible();
				return false;
			});
	}

	/** Constructor */
	{
		this.initializeFolder();
		this.toggleVisible(true);
		// Prefixを付けるために、「一旦閉じた後に」開く
		if(m_current_tag.length > 0 &&
			this.child.text().toLowerCase().indexOf(m_current_tag.toLowerCase()) >= 0)
		{
			this.toggleVisible(true);
		}
	}
}

////////////////////////////////////////////////////////////

function convertTag()
{
	for(var i = m_sections.length; --i >= 0; m_sections[i].convertTag())
		;
}

try
{
	window.onload = function()
	{
		var currentTag = $('#tag');
		if(currentTag.length > 0)
		{
			m_current_tag = currentTag.text();
			if(!m_current_tag)
			{
				m_current_tag = '';
			}
		}
		var sections = $('.onscript .section');
		for(var i = sections.length; --i >= 0; m_sections.push(new CSection(sections[i])))
			;
		var tags = $('.onscript #nav ul:first li:has(ul)');
		for(var i = tags.length; --i >= 0; new CCategory(tags[i]))
			;
		$('#body').show();
		window.setTimeout('convertTag()', 800);
	};
}
catch(err)
{
	// WSHとして起動した場合、(windowオブジェクトがないため)ここに来る。
	if(WScript)
	{
		WScript.Echo(
			'NUE - Network Utterance Environment\n' +
			'(c)2011 danmaq All rights reserved.\n' +
			'<http://nue.sourceforge.jp/> <http://danmaq.com/> \n\n' +
			'This script doesn\'t work in Windows Script Host.');
	}
}
