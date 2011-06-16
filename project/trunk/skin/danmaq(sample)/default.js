
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
	this.id = null;
	this.toString = function()
	{
		return "[CSection]";
	}
	this.toggleVisible = function()
	{
		alert(this);
	}

	/** Constructor */
	{
		var titleBar = this.titleBar;
		var id = this.section.attr('id');
		if(id == undefined)
		{
			id = ~(Math.random() * -16777215);
		}
		this.id = id;
		var section = this;
		titleBar.click(
			function()
			{
				section.toggleVisible();
			});
		titleBar.css('cursor', 'pointer');
		// TODO : h2に下記設定
		// :hover着色
		// onClick: toggleVisible()
	}
}

var m_sections = {};

window.onload = function()
{
	var sections = $('.onscript .section');
	for(var i = sections.length; --i >= 0; )
	{
		var section = new CSection(sections[i]);
		m_sections[section.id] = section;
	}

	// TODO : コメント外す
	$('#body').attr('style', 'display: relative;');
};

// TODO : 折り畳みメニュー
// TODO : 画像の変換(XSLもまだ途中)
