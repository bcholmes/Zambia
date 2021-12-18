<?php
// Copyright (c) 2018-2019 Peter Olszowka. All rights reserved. See copyright document for more details.
$report = [];
$report['name'] = 'Participant Bio and pubname';
$report['multi'] = 'true';
$report['output_filename'] = 'participant_bios.csv';
$report['description'] = 'Show the badgeid, pubsname and bio for all participants who have indicated they are attending and interested in being assigned to sessions.';
$report['categories'] = array(
    'Participant Info Reports' => 700,
);
$report['columns'] = array(
    array("width" => "6em"),
    array("width" => "12em", "orderData" => 2),
    array("visible" => false),
    array("orderable" => false),
    array("orderable" => false)
);
$report['queries'] = [];
$report['queries']['participants'] =<<<'EOD'
SELECT
        P.badgeid, P.pubsname, CD.badgename, CD.firstname, CD.lastname, IF(instr(P.pubsname, CD.lastname) > 0, CD.lastname, substring_index(P.pubsname, ' ', -1)) AS pubsnameSort, P.bio , P.pronouns
    FROM
             Participants P
        JOIN CongoDump CD USING (badgeid)
    WHERE
        P.interested = 1
    ORDER BY
        IF(instr(P.pubsname, CD.lastname) > 0, CD.lastname, substring_index(P.pubsname, ' ', -1)),
        CD.firstname;
EOD;
$report['xsl'] =<<<'EOD'
<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output encoding="UTF-8" indent="yes" method="html" />
    <xsl:include href="xsl/reportInclude.xsl" />
    <xsl:template match="/">
        <xsl:choose>
            <xsl:when test="doc/query[@queryName='participants']/row">
                <table id="reportTable" class="report table table-sm">
                    <thead>
                        <tr style="height:2.6rem">
                            <th class="report">Badge Id</th>
                            <th class="report">Name for Publications</th>
                            <th></th>
                            <th class="report">Pronouns</th>
                            <th class="report">Biography</th>
                        </tr>
                    </thead>
                    <xsl:apply-templates select="doc/query[@queryName='participants']/row" />
                </table>
            </xsl:when>
            <xsl:otherwise>
                <div class="text-info">No results found.</div>
            </xsl:otherwise>                    
        </xsl:choose>
    </xsl:template>

    <xsl:template match="doc/query[@queryName='participants']/row">
        <tr>
            <td class="report"><xsl:call-template name="showBadgeid"><xsl:with-param name="badgeid" select="@badgeid"/></xsl:call-template></td>
            <td class="report">
                <xsl:choose>
                    <xsl:when test="@pubsname != ''">
                        <xsl:value-of select="@pubsname" />
                    </xsl:when>
                    <xsl:when test="@badgename != ''">
                        <xsl:value-of select="@badgename" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@firstname" /><xsl:text> </xsl:text><xsl:value-of select="@lastname" />
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td class="report"><xsl:value-of select="@pubsnameSort" /></td>
            <td class="report"><xsl:value-of select="@pronouns" /></td>
            <td class="report"><xsl:value-of select="@bio" /></td>
        </tr>
    </xsl:template>
</xsl:stylesheet>
EOD;
