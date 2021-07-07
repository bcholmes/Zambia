<?php
$report = [];
$report['name'] = 'Leaderboard Report';
$report['multi'] = 'true';
$report['output_filename'] = 'leaderboard.csv';
$report['description'] = 'Statistics for folks scheduling and liking panels.';
$report['categories'] = array(
    'Events Reports' => 175,
    'Programming Reports' => 175,
);
$report['queries'] = [];
$report['queries']['sessions'] =<<<'EOD'
SELECT
        S.sessionid, S.title, T.trackname, TY.typename, 
        COUNT(L.scheduled) AS scheduled, COUNT(L.liked) AS liked 
    FROM
             Sessions S
        JOIN Tracks T USING (trackid)
        JOIN Types TY USING (typeid)
        JOIN Schedule SCH USING (sessionid)
   LEFT JOIN Leaderboard L USING (sessionid)
    GROUP BY
        sessionid 
    ORDER BY
        SCH.starttime;
EOD;
$report['xsl'] =<<<'EOD'
<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output encoding="UTF-8" indent="yes" method="html" />
    <xsl:include href="xsl/reportInclude.xsl" />
    <xsl:template match="/">
        <xsl:choose>
            <xsl:when test="doc/query[@queryName='sessions']/row">
                <table id="reportTable" class="table table-sm table-bordered">
                    <thead>
                        <tr class="table-primary">
                            <th class="report">Session ID</th>
                            <th class="report">Title</th>
                            <th class="report">Track</th>
                            <th class="report">Type</th>
                            <th class="report">Scheduled</th>
                            <th class="report">Liked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="doc/query[@queryName='sessions']/row" />
                    </tbody>
                </table>
            </xsl:when>
            <xsl:otherwise>
                <div class="alert alert-danger">No results found.</div>
            </xsl:otherwise>                    
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="doc/query[@queryName='sessions']/row">
        <tr class="report">
            <td class="report" >
                <xsl:call-template name="showSessionid">
                    <xsl:with-param name="sessionid" select = "@sessionid" />
                </xsl:call-template>
            </td>
            <td class="report" >
                <xsl:call-template name="showSessionTitle">
                    <xsl:with-param name="sessionid" select = "@sessionid" />
                    <xsl:with-param name="title" select = "@title" />
                </xsl:call-template>
            </td>
            <td class="report" >
                <xsl:value-of select="@trackname" />
            </td>
            <td class="report" >
                <xsl:value-of select="@typename" />
            </td>
            <td class="report" >
                <xsl:value-of select="@scheduled" />
            </td>
            <td class="report" >
                <xsl:value-of select="@liked" />
            </td>
        </tr>
    </xsl:template>
</xsl:stylesheet>
EOD;
