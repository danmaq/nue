var FADETIME = 500;


////////////////////////////////////////////////////////////

/**
 *	記事ウィンドウ クラス。
 *
 *	@param {Object} section 記事DOMオブジェクト
 */
function CSection(section)
{
	this.visible = true;
	this.section = $(section);
	this.article = this.section.find('.article');
	this.titleBar = this.section.find('h2.title');
	this.toString = function()
	{
		return "[CSection]";
	}
	this.toggleVisible = function()
	{
		this.visible = !this.visible;
		var lamp = 'solid';
		if(this.visible)
		{
			this.article.show(FADETIME);
		}
		else
		{
			lamp = 'double';
			this.article.hide(FADETIME);
		}
		this.titleBar.css('border-left-style', lamp);
	}

	/** Constructor */
	{
		var getFalse = function()
		{
			return false;
		};
		var titleBar = this.titleBar;
		var section = this;
		titleBar.click(
			function()
			{
				section.toggleVisible();
			});
		titleBar.mousedown(getFalse);
		titleBar.select(getFalse);
		titleBar.css('cursor', 'pointer');
		titleBar.css('user-select', 'none');
		// TODO : h2に下記設定
		// :hover着色
		// onClick: toggleVisible()
	}
}

window.onload = function()
{
	var sections = $('.onscript .section');
	for(var i = sections.length; --i >= 0; )
	{
		new CSection(sections[i]);
	}

	// TODO : コメント外す
	$('#body').attr('style', 'display: relative;');
};

// TODO : 折り畳みメニュー
// TODO : 画像の変換(XSLもまだ途中)
