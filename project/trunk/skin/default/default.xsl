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

		<html>
			<head>
				<meta charset="UTF-8" />
				<title><xsl:value-of select="@title" /> - <xsl:value-of select="@site" /></title>
				<link href="./" rel="Start"/>
			</head>
			<body>
				<h1><xsl:value-of select="@site" /></h1>
				<xsl:apply-templates select="topic" />
				<hr />
				<address>Network Utterance Environment version 0.0.4<br />by danmaq</address>
			</body>
		</html>
	</xsl:template>

	<!-- トピック。 -->
	<xsl:template match="topic">
		<h2><xsl:value-of select="@title" /></h2>
		<div>
			<xsl:apply-templates select="p" />
		</div>
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
