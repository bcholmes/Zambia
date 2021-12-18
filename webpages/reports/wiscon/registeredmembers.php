<?php
$report = [];
$report['name'] = 'WisCon: Registered Members Report';
$report['multi'] = 'true';
$report['output_filename'] = 'conflictnotreg.csv';
$report['description'] = 'This report shows registered members and their links to records in the registration system.';
$report['categories'] = array(
    'Registration Reports' => 380,
);
$report['columns'] = array(
    array("width" => "7em"),
    array("width" => "17em"),
    array("width" => "17em"),
    array("width" => "3em"),
    array("width" => "17em")
);
$report['queries'] = [];
$report['queries']['participants'] =<<<'EOD'
SELECT
        '1' as category, P.badgeid, IFNULL(CD.badgename, '') as badgename,
        concat(CD.firstname,' ',CD.lastname) AS name,
        IFNULL(CD.regtype, ' ') AS regtype, OI.for_name as for_name, OI.order_id
    FROM 
            Participants P 
        JOIN CongoDump CD USING (badgeid)
        JOIN reg_program_link L USING (badgeid)
        JOIN reg_order_item OI
    WHERE
	     CD.regtype != '' && CD.regtype is not null
     AND L.order_item_id = OI.id
UNION
SELECT
        '2' as category, P.badgeid, IFNULL(CD.badgename, '') as badgename,
        concat(CD.firstname,' ',CD.lastname) AS name,
        IFNULL(CD.regtype, ' '), '' as for_name, '' as order_id
    FROM 
            Participants P 
        JOIN CongoDump CD USING (badgeid)
    WHERE
	     CD.regtype != '' && CD.regtype is not null
         AND badgeid NOT IN (select badgeid from reg_program_link)
UNION
SELECT
        '3' as category, '' as badgeid, '' as badgename,
        '' AS name,
        OF.title as regtype, OI.for_name, OI.order_id
    FROM 
        reg_order_item OI,
        reg_offering OF,
        reg_order O
  WHERE OF.id = OI.offering_id
        AND OF.is_membership = 'Y'
        AND O.id = OI.order_id
        AND O.status in ('PAID', 'CHECKED_OUT')
        AND OI.id NOT IN (select order_item_id from reg_program_link)
ORDER BY
    badgeid, badgename, for_name;
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
                        <tr class="table-primary">
                            <th>Badge ID</th>
                            <th>Name (Programming)</th>
                            <th>Name (Registration)</th>
                            <th>Order #</th>
                            <th>Reg Type</th>
                        </tr>
                    </thead>
                    <xsl:apply-templates select="doc/query[@queryName='participants']/row"/>
                </table>
            </xsl:when>
            <xsl:otherwise>
                <div class="text-info">No results found.</div>
            </xsl:otherwise>                    
        </xsl:choose>
    </xsl:template>

    <xsl:template match="doc/query[@queryName='participants']/row">
        <tr>
            <td><xsl:call-template name="showBadgeid"><xsl:with-param name="badgeid" select="@badgeid"/></xsl:call-template></td>
            <td>
                <xsl:choose>
                    <xsl:when test="doc/query[@queryName='participants']/row/badgename=''">
                        <xsl:value-of select="@name"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@badgename"/>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td><xsl:value-of select="@for_name"/></td>
            <td><xsl:value-of select="@order_id"/></td>
            <td><xsl:value-of select="@regtype"/></td>
        </tr>
    </xsl:template>
</xsl:stylesheet>
EOD;
