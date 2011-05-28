<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns="http://www.w3.org/1999/xhtml"
	exclude-result-prefixes="xhtml">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />

	<!-- メイン。 -->
	<xsl:template match="/body">

		<!-- HTML5のためのDOCTYPE宣言。 -->
		<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;
</xsl:text>
		<!-- 出力のインデントが乱れるため、意図して改行しています。 -->

		<html xml:lang="ja">
			<head>
				<meta charset="UTF-8" />
				<meta http-equiv="X-UA-Compatible" content="IE=edge" />
				<meta name="application-name" content="Network Utterance Environment" />
				<meta name="author" content="danmaq" />
				<meta name="msapplication-navbutton-color" content="#BCC0DD" />
				<title><xsl:value-of select="@title" /> - <xsl:value-of select="@site" /></title>
				<link href="./" rel="Start" />
				<link href="./skin/default/default.css" rel="StyleSheet" />
				<link href="http://twitter.com/danmaq" rev="made" />
				<xsl:comment> 評価中 </xsl:comment>
			</head>
			<body>
				<h1><xsl:value-of select="@site" /></h1>
				<xsl:apply-templates select="topic" />
				<hr />
				<address><a href="http://nue.sourceforge.jp/">Network Utterance Environment</a> version <xsl:value-of select="@ver" /><br />by danmaq</address>
			</body>
		</html>
	</xsl:template>

	<!-- トピック。 -->
	<xsl:template match="topic">
		<section>
			<h2><xsl:value-of select="@title" /></h2>
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
