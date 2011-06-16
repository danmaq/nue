// alert('ほげ');

/**
 *	記事ウィンドウ クラス。
 *
 *	@param {Object} section 記事DOMオブジェクト
 */
function CSection(section)
{
	this.section = section;
	return this;
}

var m_sections = new Array();

window.onload = function()
{
	var sections = $('.section');
	for(var i = sections.length; --i >= 0; )
	{
		this.m_sections.push(new CSection(item));
	}
	// TODO : 
	alert($('#body'));
};
