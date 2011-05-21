<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xml:lang="ja">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;</xsl:text>
		<html>
			<xsl:template match="processing-instruction()"/>
			<xsl:template match="@*|node()" priority="-1.0">
				<xsl:copy><xsl:apply-templates select="@*|node()"/></xsl:copy>
			</xsl:template>
		</html>
	</xsl:template>
</xsl:stylesheet>
