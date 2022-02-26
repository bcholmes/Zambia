<?php
// Copyright (c) 2018-2019 Peter Olszowka. All rights reserved. See copyright document for more details.
$report = [];
$report['name'] = 'Participant availablity';
$report['multi'] = 'true';
$report['output_filename'] = 'participant_availability.csv';
$report['description'] = 'When they said they were available.';
$report['categories'] = array(
    'Participant Info Reports' => 920,
);
$report['columns'] = array(
    null,
    array("orderData" => 2),
    array("visible" => false),
    array("orderable" => false)
);
$report['queries'] = [];
$report['queries']['participants'] =<<<'EOD'
SELECT
        P.badgeid, CD.badgename, P.pubsname, IF(INSTR(P.pubsname, CD.lastname) > 0, CD.lastname, SUBSTRING_INDEX(P.pubsname, ' ', -1)) AS pubsnameSort,
        concat(CD.firstname,' ',CD.lastname) AS name
    FROM
             Participants P
        JOIN CongoDump CD USING (badgeid)
    WHERE
        P.interested = 1
    ORDER BY
         IF(INSTR(P.pubsname, CD.lastname) > 0, CD.lastname, SUBSTRING_INDEX(P.pubsname, ' ', -1)),
         CD.firstname;
EOD;
$report['queries']['times'] =<<<'EOD'
SELECT
        PAT.badgeid,
        DATE_FORMAT(ADDTIME('$ConStartDatim$',PAT.starttime),'%a %l:%i %p') AS starttime, 
        DATE_FORMAT(ADDTIME('$ConStartDatim$',PAT.endtime),'%a %l:%i %p') AS endtime
    FROM
             ParticipantAvailabilityTimes PAT
        JOIN Participants P USING (badgeid)
    WHERE
        P.interested=1;
EOD;
$report['xsl'] =<<<'EOD'
<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output encoding="UTF-8" indent="yes" method="html" />
    <xsl:include href="xsl/reportInclude.xsl" />
    <xsl:template match="/">
        <xsl:choose>
            <xsl:when test="doc/query[@queryName='participants']/row">
                <table id="reportTable" class="table table-sm table-bordered">
                    <thead>
                        <tr style="height:2.6rem">
                            <th>Badge ID</th>
                            <th>Name for Publications</th>
                            <th></th>
                            <th>Available Times</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="doc/query[@queryName='participants']/row" />
                    </tbody>
                </table>
            </xsl:when>
            <xsl:otherwise>
                <div class="alert alert-danger">No results found.</div>
            </xsl:otherwise>                    
        </xsl:choose>
    </xsl:template>

    <xsl:template match="doc/query[@queryName='participants']/row">
        <xsl:variable name="badgeid" select="@badgeid" />
        <tr id="badgeid{@badgeid}" >
            <td><xsl:value-of select="@badgeid" /></td>
            <td>
                <xsl:call-template name="showLinkedPubsname">
                    <xsl:with-param name="badgeid" select = "@badgeid" />
                    <xsl:with-param name="pubsname" select = "@pubsname" />
                    <xsl:with-param name="pubsname" select = "@badgename" />
                    <xsl:with-param name="pubsname" select = "@name" />
                </xsl:call-template>
            </td>
            <td><xsl:value-of select="@pubsnameSort" /></td>
            <td>
                <xsl:choose>
                    <xsl:when test="/doc/query[@queryName='times']/row[@badgeid=$badgeid]">
                        <xsl:apply-templates select="/doc/query[@queryName='times']/row[@badgeid=$badgeid]" />
                    </xsl:when>
                    <xsl:otherwise>
                       <xsl:text>NULL</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="doc/query[@queryName='times']/row">
        <div>
            <xsl:value-of select="@starttime" />
            <xsl:text> &#8211; </xsl:text>
            <xsl:value-of select="@endtime" />
        </div>
    </xsl:template>
</xsl:stylesheet>
EOD;
