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
				</xsl:if>
				<meta name="application-name" content="Network Utterance Environment" />
				<meta name="author" content="danmaq" />
				<meta name="msapplication-navbutton-color" content="#BCC0DD" />
				<title>
					<xsl:if test="@title and string-length(@title) > 0"><xsl:value-of select="@title" /> - </xsl:if>
					<xsl:value-of select="@site" />
				</title>
				<link href="./" rel="Start" />
				<link href="./skin/default/default.css" rel="StyleSheet" />
				<link href="http://twitter.com/danmaq" rel="Author" />
				<xsl:comment> 評価中 </xsl:comment>
			</head>
			<body>
				<header>
					<h1>
						<a href="./" rel="Start"><xsl:value-of select="@site" /></a>
					</h1>
					<xsl:apply-templates select="user|serch" />
					<xsl:apply-templates select="topic" />
				</header>
				<nav>
				</nav>
				<footer>
					<hr />
					<address><a href="http://nue.sourceforge.jp/">Network Utterance Environment</a> version <xsl:value-of select="@ver" /><br />by danmaq</address>
				</footer>
			</body>
		</html>
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
	<xsl:template match="user">
		<!-- TODO : トピック使いまわせないか？ -->
		<section>
			<h2>タグ検索</h2>
			<article>
				<form action="./" method="get">
					<p>
						<label for="t">キーワード</label>
						<input type="text" id="t" name="t" value="{@tag}" maxlength="255" placeholder="255字以内" />
						<input type="submit" value="検索" />
					</p>
					<xsl:if test="@tag">
						<p>現在の検索タグ: <em><xsl:value-of select="@tag" /></em></p>
					</xsl:if>
				</form>
			</article>
		</section>
	</xsl:template>

	<!-- トピック。 -->
	<xsl:template match="topic">
		<section>
			<h2>
				<xsl:choose>
					<xsl:when test="@id">
						<a href="?{@id}"><xsl:value-of select="@title" /></a>
					</xsl:when>
					<xsl:otherwise><xsl:value-of select="@title" /></xsl:otherwise>
				</xsl:choose>
			</h2>
			<article>
				<xsl:apply-templates select="p|form" />
			</article>
		</section>
	</xsl:template>

	<!-- フォーム。 -->
	<xsl:template match="form">
		<form onsubmit="return true;">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates select="p" />
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
