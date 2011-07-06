<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns="http://www.w3.org/1999/xhtml"
	exclude-result-prefixes="xhtml">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" media-type="application/xhtml+xml" />

	<!-- メイン。 -->
	<xsl:template match="/body">

		<!-- HTML5のためのDOCTYPE宣言。 -->
		<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;
</xsl:text>
		<!-- 出力のインデントが乱れるため、意図して改行しています。 -->

		<html xml:lang="ja">
			<head>
				<meta charset="UTF-8" />
				<xsl:if test="contains(@ua, ' IE ') or contains(@ua, ' MSIE ')">
					<meta http-equiv="X-UA-Compatible" content="IE=edge" />
					<meta name="msapplication-navbutton-color" content="#BCC0DD" />
				</xsl:if>
				<meta name="application-name" content="Network Utterance Environment" />
				<meta name="author" content="danmaq" />
				<title>
					<xsl:if test="@title and string-length(@title) > 0"><xsl:value-of select="@title" /> - </xsl:if>
					<xsl:value-of select="@site" />
				</title>
				<link href="skin/default/default.css" rel="StyleSheet" />
				<link href="http://twitter.com/danmaq" rel="Author" />
				<xsl:comment> 評価中 </xsl:comment>
			</head>
			<body>
				<header>
					<h1>
						<a href="./"><xsl:value-of select="@site" /></a>
					</h1>
					<xsl:apply-templates select="user|search" />
				</header>
				<xsl:apply-templates select="topic" />
				<xsl:apply-templates select="category" />
				<xsl:apply-templates select="pager" />
				<footer>
					<hr />
					<address><a href="http://nue.sourceforge.jp/">Network Utterance Environment</a> version <xsl:value-of select="@ver" /><br />by danmaq</address>
				</footer>
			</body>
		</html>
	</xsl:template>


	<!-- ページャ情報。 -->
	<xsl:template match="pager">
		<xsl:call-template name="topic">
			<xsl:with-param name="title">Page</xsl:with-param>
			<xsl:with-param name="body">
				<p>
					<xsl:variable name="query"><xsl:if test="../search/@tag">/<xsl:value-of select="../search/@tag" /></xsl:if>&amp;</xsl:variable>
					<xsl:if test="@page &gt; 0">
						<a href="?{$query}({@page - 1}/{@tpp})">&lt;前のページへ</a>
					</xsl:if>
					<span> | <xsl:value-of select="@page + 1" />/<xsl:value-of select="@max" />ページ目(<xsl:value-of select="@tpp" />件/ページ:<xsl:value-of select="@topics" />件) | </span>
					<xsl:if test="@page + 1 &lt; @max">
						<a href="?{$query}({@page + 1}/{@tpp})">次のページへ&gt;</a>
					</xsl:if>
				</p>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<!-- ログオン情報。 -->
	<xsl:template match="user">
		<p>
			<xsl:choose>
				<xsl:when test="@id and @name">
					<a href="?f=core/user/pref"><xsl:value-of select="@name" /> さん</a> | <a href="?f=core/user/logoff">ログオフ</a>
				</xsl:when>
				<xsl:when test="not(@id) and @name"><xsl:value-of select="@name" /> さん</xsl:when>
				<xsl:otherwise>ゲストさん | <a href="?f=core/user/new">ログオン / サインアップ</a></xsl:otherwise>
			</xsl:choose>
		</p>
	</xsl:template>

	<!-- 検索。 -->
	<xsl:template match="search">
		<xsl:call-template name="topic">
			<xsl:with-param name="title">タグ検索</xsl:with-param>
			<xsl:with-param name="body">
				<form action="./" method="get">
					<p>
						<input class="text" type="text" id="t" name="t" value="{@tag}" maxlength="255" placeholder="255字以内" />
						<input type="submit" value="検索" />
					</p>
					<p>
						<xsl:if test="@tag">現在の検索タグ: <em id="tag"><xsl:value-of select="@tag" /></em><br /></xsl:if>
						<a href="?f=core/tag/all">登録タグ一覧</a>
					</p>
				</form>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<!-- カテゴリ。 -->
	<xsl:template match="category">
		<nav>
			<h2>カテゴリ</h2>
			<ul><xsl:apply-templates select="li" /></ul>
		</nav>
	</xsl:template>

	<!-- カテゴリ。 -->
	<xsl:template match="li">
		<li>
			<xsl:choose>
				<xsl:when test="count(ul) = 0 and @href">
					<a href="?/{@href}"><xsl:value-of select="." /></a>
				</xsl:when>
				<xsl:otherwise><xsl:apply-templates /></xsl:otherwise>
			</xsl:choose>
		</li>
	</xsl:template>

	<!-- カテゴリ。 -->
	<xsl:template match="ul">
		<xsl:if test="@title">
			<h3><xsl:value-of select="@title" /></h3>
		</xsl:if>
		<xsl:if test="lh">
			<a href="?/{lh/@href}"><xsl:value-of select="lh" /></a>
		</xsl:if>
		<ul><xsl:apply-templates select="li" /></ul>
	</xsl:template>

	<!-- 番号付きリスト。 -->
	<xsl:template match="ol">
		<xsl:if test="@title">
			<h3><xsl:value-of select="@title" /></h3>
		</xsl:if>
		<ol><xsl:apply-templates select="li" /></ol>
	</xsl:template>

	<!-- トピック。 -->
	<xsl:template name="topic" match="topic">
		<xsl:param name="title"><xsl:value-of select="@title" /></xsl:param>
		<xsl:param name="body">
			<xsl:apply-templates select="p|ul|ol|form" />
		</xsl:param>
		<section>
			<h2>
				<xsl:if test="@created">[<xsl:value-of select="@created" />]</xsl:if>
				<xsl:choose>
					<xsl:when test="@id">
						<a href="?{@id}"><xsl:value-of select="$title" /></a>
					</xsl:when>
					<xsl:otherwise><xsl:value-of select="$title" /></xsl:otherwise>
				</xsl:choose>
			</h2>
			<article>
				<xsl:copy-of select="$body" />
			</article>
		</section>
	</xsl:template>

	<!-- フォーム。 -->
	<xsl:template match="form">
		<form onsubmit="return true;">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates select="p|ul|ol" />
		</form>
	</xsl:template>

	<!-- 段落。 -->
	<xsl:template match="p">
		<xsl:if test="@title">
			<h3><xsl:value-of select="@title" /></h3>
		</xsl:if>
		<p>
			<xsl:apply-templates />
		</p>
	</xsl:template>

	<!-- HTML名前空間を持つモノは丸投げしてしまう。 -->
	<xsl:template match="xhtml:*">
		<xsl:element name="{local-name()}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
