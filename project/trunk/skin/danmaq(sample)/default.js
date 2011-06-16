
////////////////////////////////////////////////////////////

/**
 *	記事ウィンドウ クラス。
 *
 *	@param {Object} section 記事DOMオブジェクト
 */
function CSection(section)
{
	this.section = $(section);
	this.titleBar = this.section.find('h2.title');
	this.toString = function()
	{
		return "[CSection]";
	}
	this.toggleVisible = function()
	{
		alert(this.titleBar);
	}

	/** Constructor */
	{
		var titleBar = this.titleBar;
		titleBar.css('cursor', 'pointer');
		titleBar.click(this.toggleVisible);
		// TODO : h2に下記設定
		// :hover着色
		// onClick: toggleVisible()
	}
}

var m_sections = new Array();

window.onload = function()
{
	var sections = $('.onscript .section');
	for(var i = sections.length; --i >= 0; )
	{
		m_sections.push(new CSection(sections[i]));
	}

	// TODO : コメント外す
	//$('#body').attr('style', 'display: relative;');
};

// TODO : 折り畳みメニュー
// TODO : 画像の変換(XSLもまだ途中)
